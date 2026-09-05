#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SONGCHART="$ROOT/songchart"
DEV_PROJECT="${SONGCHART_DEV_PROJECT:-songchart-dev}"
DEV_COMPOSE="$ROOT/compose.dev.yml"
CODESPACES_COMPOSE="$ROOT/compose.codespaces.yml"

impact_json="$($SONGCHART impact --diff --json)"

json_array_lines(){
    local key="$1"
    local compose=(docker compose -p "$DEV_PROJECT" -f "$DEV_COMPOSE")
    if [[ "${CODESPACES:-false}" == "true" ]]; then
        [[ -n "${CODESPACE_NAME:-}" ]] || { printf 'CODESPACE_NAME is required in GitHub Codespaces.\n' >&2; exit 1; }
        export SONGCHART_CODESPACES_APP_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
        compose+=( -f "$CODESPACES_COMPOSE" )
    fi
    "${compose[@]}" run --rm -T app php -r '$key=$argv[1]; $data=json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR); foreach (($data[$key] ?? []) as $value) { echo $value, PHP_EOL; }' "$key"
}

mapfile -t checks < <(printf '%s\n' "$impact_json" | json_array_lines required_focused_checks)
mapfile -t changed_paths < <(printf '%s\n' "$impact_json" | json_array_lines changed_paths)

printf '[SongChart impact verify] Running fast preflight guards.\n'
git -C "$ROOT" diff --check

if git -C "$ROOT" rev-parse --abbrev-ref --symbolic-full-name '@{upstream}' >/dev/null 2>&1; then
    read -r behind ahead < <(git -C "$ROOT" rev-list --left-right --count '@{upstream}...HEAD')
    if (( behind > 0 )); then
        if (( ahead > 0 )); then
            printf '[SongChart impact verify] Branch is diverged from its upstream (%d behind, %d ahead). Rebase/synchronize before verification.\n' "$behind" "$ahead" >&2
        else
            printf '[SongChart impact verify] Branch is behind its upstream by %d commit(s). Synchronize before verification.\n' "$behind" >&2
        fi
        exit 1
    fi
fi

generated_status="$(git -C "$ROOT" status --short --untracked-files=all -- docs/project/generated)"
if [[ -n "$generated_status" ]]; then
    printf '[SongChart impact verify] Generated repository authority has uncommitted changes. Commit or restore generated authority before pre-closure verification.\n' >&2
    printf '%s\n' "$generated_status" >&2
    exit 1
fi

php_paths=()
for path in "${changed_paths[@]}"; do
    [[ "$path" == *.php ]] || continue
    [[ -f "$ROOT/$path" ]] || continue
    php_paths+=("$path")
done

if [[ ${#php_paths[@]} -gt 0 ]]; then
    printf '[SongChart impact verify] Checking Pint on changed PHP paths before the expensive lane.\n'
    "$SONGCHART" composer exec pint -- --test "${php_paths[@]}"
fi

printf '[SongChart impact verify] Checking repository compiler and verification-consumer ownership before the expensive lane.\n'
"$SONGCHART" composer repository-compiler:verify

if [[ ${#checks[@]} -eq 0 ]]; then
    printf '[SongChart impact verify] No focused checks resolved for the current diff.\n'
    exit 0
fi

closure_checks=()
filtered_checks=()
for check in "${checks[@]}"; do
    case "$check" in
        'composer canonical:verify'|'songchart verify'|'songchart close')
            closure_checks+=("$check")
            ;;
        *)
            filtered_checks+=("$check")
            ;;
    esac
done
checks=("${filtered_checks[@]}")

printf '[SongChart impact verify] Required pre-closure checks (%d):\n' "${#checks[@]}"
printf -- '- %s\n' "${checks[@]}"
if [[ ${#closure_checks[@]} -gt 0 ]]; then
    printf '[SongChart impact verify] Deferred closure-only checks (%d):\n' "${#closure_checks[@]}"
    printf -- '- %s\n' "${closure_checks[@]}"
    printf '[SongChart impact verify] Canonical verification is closure-only; run it through candidate/close on an exact committed tree.\n'
fi

focused_image_ready=false
run_focused_test(){
    local target="$1"
    if [[ "$focused_image_ready" == true ]]; then
        "$SONGCHART" dev test --no-build "$target"
    else
        "$SONGCHART" dev test "$target"
        focused_image_ready=true
    fi
}

run_test_target(){
    local target="$1"
    local matched=false

    while IFS= read -r path; do
        [[ -n "$path" ]] || continue
        matched=true
        run_focused_test "$path"
    done < <(git -C "$ROOT" ls-files -- "$target")

    if [[ "$matched" != true ]]; then
        printf '[SongChart impact verify] Test target did not resolve to tracked files: %s\n' "$target" >&2
        exit 1
    fi
}

for check in "${checks[@]}"; do
    printf '\n[SongChart impact verify] Running: %s\n' "$check"

    case "$check" in
        'composer stage:verify')
            "$SONGCHART" test
            ;;
        composer\ *)
            read -r -a args <<< "${check#composer }"
            "$SONGCHART" composer "${args[@]}"
            ;;
        songchart\ *)
            read -r -a args <<< "${check#songchart }"
            "$SONGCHART" "${args[@]}"
            ;;
        tests/*)
            run_test_target "$check"
            ;;
        *)
            printf '[SongChart impact verify] Unsupported impact check target: %s\n' "$check" >&2
            exit 1
            ;;
    esac
done

printf '\n[SongChart impact verify] PASSED pre-closure verification.\n'

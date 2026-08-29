#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SONGCHART="$ROOT/songchart"

impact_json="$($SONGCHART impact --diff --json)"
mapfile -t checks < <(printf '%s\n' "$impact_json" | python3 -c 'import json, sys; data=json.load(sys.stdin); print("\n".join(data.get("required_focused_checks", [])))')

if [[ ${#checks[@]} -eq 0 ]]; then
    printf '[SongChart impact verify] No focused checks resolved for the current diff.\n'
    exit 0
fi

printf '[SongChart impact verify] Required focused checks (%d):\n' "${#checks[@]}"
printf -- '- %s\n' "${checks[@]}"

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
        'composer canonical:verify')
            "$SONGCHART" verify
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

printf '\n[SongChart impact verify] PASSED.\n'

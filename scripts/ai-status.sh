#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

value_from_json(){
  local file="$1" key="$2"
  grep -m1 -E '"'"$key"'"[[:space:]]*:' "$file" 2>/dev/null | sed -E 's/^[^:]+:[[:space:]]*"?([^",}]*)"?.*/\1/' | xargs || true
}

branch="$(git branch --show-current 2>/dev/null || printf 'unknown')"
head="$(git rev-parse --short HEAD 2>/dev/null || printf 'unknown')"
modified="$(git status --porcelain --untracked-files=all 2>/dev/null | wc -l | tr -d ' ')"
upstream="$(git rev-parse --abbrev-ref --symbolic-full-name '@{u}' 2>/dev/null || true)"
ahead=0
behind=0
if [[ -n "$upstream" ]]; then
  read -r ahead behind < <(git rev-list --left-right --count HEAD..."$upstream" 2>/dev/null || printf '0 0')
fi

environment="local-linux"
if [[ "${CODESPACES:-false}" == "true" ]]; then
  environment="github-codespaces"
elif [[ "$(uname -s 2>/dev/null || true)" == "Darwin" ]]; then
  environment="macos"
elif grep -qi microsoft /proc/version 2>/dev/null; then
  environment="windows-wsl"
fi

candidate_file="$ROOT/candidate-verification.json"
stage="unknown"
candidate="unknown"
closure_ready="unknown"
verified_at="never"
not_run=0
passed=0
failed=0
if [[ -f "$candidate_file" ]]; then
  stage="$(value_from_json "$candidate_file" stage)"
  candidate="$(value_from_json "$candidate_file" candidate)"
  closure_ready="$(grep -m1 -E '"closure_ready"[[:space:]]*:' "$candidate_file" | sed -E 's/.*:[[:space:]]*(true|false).*/\1/' || true)"
  verified_raw="$(grep -m1 -E '"verified_at"[[:space:]]*:' "$candidate_file" | sed -E 's/.*:[[:space:]]*(.*),?$/\1/' | tr -d '" ,' || true)"
  [[ -n "$verified_raw" && "$verified_raw" != "null" ]] && verified_at="$verified_raw"
  not_run="$(grep -c ': "not_run"' "$candidate_file" || true)"
  passed="$(grep -c ': "passed"' "$candidate_file" || true)"
  failed="$(grep -c ': "failed"' "$candidate_file" || true)"
fi

checkpoint_state="MISSING"
checkpoint_detail="DEVELOPMENT_STATE.md is missing"
checkpoint_file="$ROOT/docs/project/DEVELOPMENT_STATE.md"
if [[ -f "$checkpoint_file" ]]; then
  checkpoint_stage="$(grep -A4 '^## Current stage' "$checkpoint_file" | grep -m1 -E 'Stage[[:space:]]+`?[0-9]+(\.[0-9]+)*|`[0-9]+(\.[0-9]+)*' | sed -E 's/.*`?([0-9]+(\.[0-9]+)*)`?.*/\1/' || true)"
  if [[ -n "$checkpoint_stage" && "$checkpoint_stage" == "$stage" ]]; then
    checkpoint_state="SYNCED"
    checkpoint_detail="operational checkpoint matches candidate stage"
  else
    checkpoint_state="DRIFT"
    checkpoint_detail="checkpoint stage=${checkpoint_stage:-unknown}, candidate stage=$stage"
  fi
fi

context_file="$ROOT/docs/project/generated/project-context.json"
context_state="MISSING"
context_detail="generated context file is missing"
context_sources=(
  composer.json composer.lock compose.dev.yml compose.verify.yml scripts/songchart.ps1
  docs/project/stack/runtime-environments.json
  docs/project/domain/schema-ownership.json
  docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md
  docs/project/engineering/ai-development-contract.json
  docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md
  docs/project/engineering/verification-command-surface.json
  docs/project/engineering/verification-topology.json
  docs/project/stack/release-pipeline-contract.json
  database/migrations database/seeders
)
if [[ -f "$context_file" ]]; then
  source_dirty="$(git status --porcelain --untracked-files=all -- "${context_sources[@]}" 2>/dev/null || true)"
  context_dirty="$(git status --porcelain --untracked-files=all -- docs/project/generated/project-context.json 2>/dev/null || true)"
  source_commit="$(git log -1 --format=%H -- "${context_sources[@]}" 2>/dev/null || true)"
  context_commit="$(git log -1 --format=%H -- docs/project/generated/project-context.json 2>/dev/null || true)"
  if [[ -n "$source_dirty" || -n "$context_dirty" ]]; then
    context_state="STALE"
    context_detail="registered context input or generated context has uncommitted changes"
  elif [[ -n "$source_commit" && -n "$context_commit" ]] && git merge-base --is-ancestor "$source_commit" "$context_commit" >/dev/null 2>&1; then
    context_state="FRESH"
    context_detail="committed generated context covers latest registered source input"
  else
    context_state="STALE"
    context_detail="registered source input is newer than committed generated context"
  fi
fi

docker_available=false
if command -v docker >/dev/null 2>&1 && docker version >/dev/null 2>&1; then docker_available=true; fi

dev_state="STOPPED"
dev_url="https://docker.songchart.test:8443"
if [[ "${CODESPACES:-false}" == "true" && -n "${CODESPACE_NAME:-}" ]]; then
  dev_url="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
fi
if [[ "$docker_available" == true && -f "$ROOT/.env.docker" ]]; then
  compose=(-p songchart-dev -f "$ROOT/compose.dev.yml")
  if [[ "${CODESPACES:-false}" == "true" && -f "$ROOT/compose.codespaces.yml" ]]; then
    export SONGCHART_CODESPACES_APP_URL="$dev_url"
    compose+=(-f "$ROOT/compose.codespaces.yml")
  fi
  if docker compose "${compose[@]}" ps --status running --services 2>/dev/null | grep -qx app; then dev_state="RUNNING"; fi
fi

demo_state="UNCONFIGURED"
demo_url="http://127.0.0.1:8001"
if [[ "${CODESPACES:-false}" == "true" && -n "${CODESPACE_NAME:-}" ]]; then
  demo_url="https://${CODESPACE_NAME}-8001.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
fi
if [[ -f "$ROOT/.env.demo" ]]; then
  demo_state="STOPPED"
  if [[ "$docker_available" == true ]] && SONGCHART_DEMO_APP_URL="$demo_url" SONGCHART_DEMO_PORT=8001 docker compose -p songchart-demo -f "$ROOT/compose.demo.yml" ps --status running --services 2>/dev/null | grep -qx app; then
    demo_state="RUNNING"
  fi
fi

codex_version="not installed"
gemini_version="not installed"
command -v codex >/dev/null 2>&1 && codex_version="$(codex --version 2>/dev/null | head -n1 || printf 'installed')"
command -v gemini >/dev/null 2>&1 && gemini_version="$(gemini --version 2>/dev/null | head -n1 || printf 'installed')"

gemini_auth="not configured"
settings_file=""
if [[ -f "$ROOT/.gemini/settings.json" ]]; then
  settings_file="$ROOT/.gemini/settings.json"
elif [[ -f "${HOME:-}/.gemini/settings.json" ]]; then
  settings_file="${HOME}/.gemini/settings.json"
fi
if [[ -n "$settings_file" ]]; then
  selected_type="$(grep -m1 -E '"selectedType"[[:space:]]*:' "$settings_file" | sed -E 's/.*:[[:space:]]*"([^"]+)".*/\1/' || true)"
  case "$selected_type" in
    oauth-personal) gemini_auth="oauth-personal (Google account; AI Pro/Ultra eligible)" ;;
    gemini-api-key) gemini_auth="gemini-api-key (separate API quota/billing)" ;;
    USE_VERTEX_AI|vertex-ai) gemini_auth="vertex-ai" ;;
    '') gemini_auth="not selected" ;;
    *) gemini_auth="$selected_type" ;;
  esac
elif [[ -n "${GEMINI_API_KEY:-}" ]]; then
  gemini_auth="gemini-api-key via environment (separate API quota/billing)"
fi

next_action="Continue current stage with focused verification."
if [[ "$modified" != "0" ]]; then
  next_action="Review/commit the current working tree before AI handoff."
elif [[ "$checkpoint_state" != "SYNCED" ]]; then
  next_action="Reconcile docs/project/DEVELOPMENT_STATE.md with the current candidate stage."
elif [[ "$context_state" != "FRESH" ]]; then
  next_action="Refresh repository context: ./songchart context --refresh-source, then review/commit generated authority."
elif [[ "$closure_ready" == "true" ]]; then
  next_action="Candidate evidence is closure-ready; proceed only through the governed closure workflow."
fi

printf 'SongChart AI Session Status\n\n'
printf '%-14s %s\n' 'Environment:' "$environment"
printf '%-14s %s / %s\n' 'Stage:' "$stage" "$candidate"
printf '%-14s %s @ %s\n' 'Branch:' "$branch" "$head"
printf '%-14s modified=%s ahead=%s behind=%s upstream=%s\n' 'Git:' "$modified" "$ahead" "$behind" "${upstream:-none}"
printf '%-14s %s — %s\n' 'Checkpoint:' "$checkpoint_state" "$checkpoint_detail"
printf '%-14s %s — %s\n' 'Context:' "$context_state" "$context_detail"
printf '%-14s %s — %s\n' 'Dev:' "$dev_state" "$dev_url"
printf '%-14s %s — %s\n' 'Demo:' "$demo_state" "$demo_url"
printf '%-14s closure_ready=%s verified_at=%s gates(passed=%s failed=%s not_run=%s)\n' 'Verification:' "$closure_ready" "$verified_at" "$passed" "$failed" "$not_run"
printf '%-14s %s\n' 'Codex:' "$codex_version"
printf '%-14s %s\n' 'Gemini:' "$gemini_version"
printf '%-14s %s\n' 'Gemini auth:' "$gemini_auth"
printf '\nNext: %s\n' "$next_action"

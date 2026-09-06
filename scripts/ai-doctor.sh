#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

redact(){
  sed -E \
    -e 's/((API[_ -]?KEY|TOKEN|PASSWORD|SECRET|AUTHORIZATION)[[:space:]]*[:=][[:space:]]*)[^[:space:],;]+/\1[REDACTED]/Ig' \
    -e 's/(Bearer[[:space:]]+)[A-Za-z0-9._~+\/-]+/\1[REDACTED]/Ig' \
    -e 's#(postgres(ql)?://)[^/@[:space:]]+(:[^/@[:space:]]+)?@#\1[REDACTED]@#Ig'
}

section(){
  printf '\n## %s\n' "$1"
}

stage_plan="$ROOT/docs/project/engineering/stage-plan.json"
current_stage="unknown"
task_contract="unknown"
if [[ -f "$stage_plan" ]]; then
  current_stage="$(sed -nE 's/^[[:space:]]*"id"[[:space:]]*:[[:space:]]*"([^"]+)".*/\1/p' "$stage_plan" | head -n 1)"
  task_contract="$(sed -nE 's/^[[:space:]]*"task_contract"[[:space:]]*:[[:space:]]*"([^"]+)".*/\1/p' "$stage_plan" | head -n 1)"
fi
[[ -n "$current_stage" ]] || current_stage="unknown"
[[ -n "$task_contract" ]] || task_contract="unknown"

printf '%s\n' '[SongChart AI doctor] === COPY FROM HERE ==='
printf '%s\n' 'SongChart AI Diagnostic Bundle'
printf 'Generated: %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')"
printf 'Current stage: %s\n' "$current_stage"
printf '%s\n' 'Safety: secret values and environment files are intentionally excluded/redacted.'

section 'Session status'
bash "$ROOT/scripts/ai-status.sh" || true

section 'Working tree'
status="$(git status --short --untracked-files=all 2>/dev/null || true)"
if [[ -n "$status" ]]; then
  printf '%s\n' "$status"
else
  printf '%s\n' 'clean'
fi

section 'Recent commits'
git log -5 --date=short --pretty='format:%h %ad %s' 2>/dev/null || printf '%s\n' 'unavailable'

section 'Current authorities'
printf '%s\n' '- PROJECT_AUTHORITY.md'
printf '%s\n' '- docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md'
printf '%s\n' '- docs/project/engineering/mcp-governance-contract.json'
printf '%s\n' '- docs/project/engineering/external-systems-registry.json'
printf -- '- %s\n' "$task_contract"
printf '%s\n' '- docs/project/generated/development-state.json (generated projection)'

section 'Authored stage plan'
if [[ -f "$stage_plan" ]]; then
  sed -n '1,120p' "$stage_plan"
else
  printf '%s\n' 'stage-plan.json missing'
fi

section 'Docker development runtime'
if ! command -v docker >/dev/null 2>&1; then
  printf '%s\n' 'Docker CLI unavailable on this host.'
elif ! docker version >/dev/null 2>&1; then
  printf '%s\n' 'Docker Engine unreachable.'
else
  project="${SONGCHART_DEV_PROJECT:-songchart-dev}"
  printf 'Compose project: %s\n' "$project"
  runtime_rows="$(docker ps -a --filter "label=com.docker.compose.project=${project}" --format '{{.Names}}\t{{.Status}}' 2>/dev/null || true)"
  if [[ -n "$runtime_rows" ]]; then
    printf '%s\n' "$runtime_rows"
  else
    printf '%s\n' 'No SongChart development containers currently exist.'
  fi
  non_running="$(docker ps -aq --filter "label=com.docker.compose.project=${project}" 2>/dev/null | while read -r id; do [[ -n "$id" ]] || continue; state="$(docker inspect -f '{{.State.Status}}' "$id" 2>/dev/null || true)"; [[ "$state" == 'running' ]] || printf '%s\n' "$id"; done)"
  if [[ -n "$non_running" ]]; then
    printf '%s\n' 'Non-running development containers detected. Safe recovery: bash scripts/recover-docker-dev-runtime.sh'
  fi
  printf '%s\n' 'Recovery removes container objects only; named volumes are preserved.'
fi

section 'Latest focused-test evidence'
focused_log="$ROOT/storage/logs/focused-test-last.log"
failure_log="$ROOT/storage/logs/postgres-test-last-failure.log"
if [[ -f "$focused_log" ]]; then
  printf 'Source: %s\n' 'storage/logs/focused-test-last.log'
  printf 'Bytes: %s\n' "$(wc -c < "$focused_log" | tr -d ' ')"
  grep -E '(^[[:space:]]*(FAIL|WARN|PASS)|FAILED|WARN|Tests:|Duration:|Exception|ERROR|file_get_contents|SongChart DB diagnostic|SongChart test evidence)' "$focused_log" \
    | tail -n 120 \
    | redact \
    || true
elif [[ -f "$failure_log" ]]; then
  printf 'Source: %s\n' 'storage/logs/postgres-test-last-failure.log'
  printf 'Bytes: %s\n' "$(wc -c < "$failure_log" | tr -d ' ')"
  grep -E '(^[[:space:]]*(FAIL|WARN|PASS)|FAILED|WARN|Tests:|Duration:|Exception|ERROR|file_get_contents|SongChart DB diagnostic|SongChart test evidence)' "$failure_log" \
    | tail -n 120 \
    | redact \
    || true
else
  printf '%s\n' 'No captured focused-test evidence found.'
fi

section 'Historical Laravel error signatures'
printf '%s\n' 'Note: entries below may predate the latest focused test and are context only.'
laravel_log="$ROOT/storage/logs/laravel.log"
if [[ -f "$laravel_log" ]]; then
  grep -E 'testing\.(ERROR|WARNING)|local\.(ERROR|WARNING)|production\.(ERROR|WARNING)' "$laravel_log" \
    | tail -n 12 \
    | redact \
    || printf '%s\n' 'No matching error signatures found.'
else
  printf '%s\n' 'No Laravel log found.'
fi

section 'Recommended handoff'
printf '%s\n' 'Give this bundle to the active SongChart development AI together with one instruction:'
printf '"Resume current Stage %s from repository authority, diagnose the narrowest blocker from this evidence, preserve development data, and specify the next governed verification command."\n' "$current_stage"

printf '\n%s\n' '[SongChart AI doctor] === END COPY ==='

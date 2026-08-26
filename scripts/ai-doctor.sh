#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

redact(){
  sed -E \
    -e 's/((API[_ -]?KEY|TOKEN|PASSWORD|SECRET|AUTHORIZATION)[[:space:]]*[:=][[:space:]]*)[^[:space:],;]+/\1[REDACTED]/Ig' \
    -e 's/(Bearer[[:space:]]+)[A-Za-z0-9._~+\/-]+/\1[REDACTED]/Ig'
}

section(){
  printf '\n## %s\n' "$1"
}

printf '%s\n' '[SongChart AI doctor] === COPY FROM HERE ==='
printf '%s\n' 'SongChart AI Diagnostic Bundle'
printf 'Generated: %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')"
printf '%s\n' 'Safety: secret values and environment files are intentionally excluded.'

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
printf '%s\n' '- docs/foundation/STAGE_18_1_TASK_CONTRACT.md'
printf '%s\n' '- docs/project/DEVELOPMENT_STATE.md'

section 'Operational checkpoint'
if [[ -f "$ROOT/docs/project/DEVELOPMENT_STATE.md" ]]; then
  awk '
    /^## Current blockers \/ risks/ {show=1}
    /^## Documentation checkpoint discipline/ {show=0}
    show {print}
  ' "$ROOT/docs/project/DEVELOPMENT_STATE.md" | head -n 80
else
  printf '%s\n' 'DEVELOPMENT_STATE.md missing'
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
printf '%s\n' 'Give this bundle to ChatGPT/Codex together with one instruction:'
printf '%s\n' '"Diagnose the current Stage 18.1 blocker from this evidence, inspect GitHub source authority, fix the narrowest root cause, and specify the next focused verification command."'

printf '\n%s\n' '[SongChart AI doctor] === END COPY ==='

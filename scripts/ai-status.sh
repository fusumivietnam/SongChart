#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

json_mode=false
runtime_mode=false
for arg in "$@"; do
  case "$arg" in
    --json) json_mode=true ;;
    --runtime) runtime_mode=true ;;
    *) printf 'Unknown ai status option: %s\n' "$arg" >&2; exit 2 ;;
  esac
done

if [[ "$runtime_mode" == true ]]; then
  if [[ "$json_mode" != true ]]; then
    printf '%s\n' 'Runtime status is machine-readable only; use ./songchart ai status --json --runtime.' >&2
    exit 2
  fi

  exec php "$ROOT/scripts/ai-runtime-status.php"
fi

if [[ "$json_mode" == true ]]; then
  exec php "$ROOT/scripts/ai-operation-plan-status.php"
fi

php "$ROOT/scripts/project-state.php"

branch="$(git branch --show-current 2>/dev/null || printf 'unknown')"
head="$(git rev-parse --short HEAD 2>/dev/null || printf 'unknown')"
modified="$(git status --porcelain --untracked-files=all 2>/dev/null | wc -l | tr -d ' ')"
upstream="$(git rev-parse --abbrev-ref --symbolic-full-name '@{u}' 2>/dev/null || true)"
ahead=0
behind=0
if [[ -n "$upstream" ]]; then
  read -r ahead behind < <(git rev-list --left-right --count HEAD..."$upstream" 2>/dev/null || printf '0 0')
fi

context_state="MISSING"
[[ -f "$ROOT/docs/project/generated/project-context.json" ]] && context_state="PRESENT"
derived_state="MISSING"
[[ -f "$ROOT/docs/project/generated/development-state.json" ]] && derived_state="PRESENT"

printf '\nSession runtime\n'
printf '%-14s %s @ %s\n' 'Branch:' "$branch" "$head"
printf '%-14s modified=%s ahead=%s behind=%s upstream=%s\n' 'Git:' "$modified" "$ahead" "$behind" "${upstream:-none}"
printf '%-14s %s\n' 'Context:' "$context_state"
printf '%-14s %s\n' 'Derived state:' "$derived_state"

if [[ "$modified" != "0" ]]; then
  printf '\nNext: reconcile or commit the working tree before handing work to another device/AI session.\n'
elif [[ "$derived_state" != "PRESENT" ]]; then
  printf '\nNext: let Auto Closure regenerate repository-derived state, or run php scripts/project-state.php --write-source in a governed prepare step.\n'
else
  printf '\nNext: resolve the live GitHub PR for this branch and resume that exact work lease; do not create duplicate work for the same semantic owner.\n'
fi

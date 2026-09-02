#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REGISTRY="$ROOT/docs/project/engineering/delivery-kernel.json"
die(){ printf 'SongChart delivery kernel: %s\n' "$*" >&2; exit 1; }
[[ -f "$REGISTRY" ]] || die 'delivery-kernel.json is missing.'
branch(){ git -C "$ROOT" branch --show-current; }
head_sha(){ git -C "$ROOT" rev-parse HEAD; }
upstream(){ git -C "$ROOT" rev-parse --abbrev-ref --symbolic-full-name '@{upstream}' 2>/dev/null || true; }
working_dirty(){ [[ -n "$(git -C "$ROOT" status --porcelain --untracked-files=all)" ]]; }
print_registry_summary(){
  python3 - "$REGISTRY" <<'PY'
import json, sys
p=sys.argv[1]
d=json.load(open(p, encoding='utf-8'))
print('Risk classes: ' + ', '.join(f"{k} {v['name']}" for k,v in d['risk_classes'].items()))
print('Gate profiles: ' + ', '.join(f"{k} {v['name']}" for k,v in d['gate_profiles'].items()))
print('Lifecycle: ' + ' -> '.join(d['lifecycle']))
PY
}
status(){
  local b h u counts tree_state
  b="$(branch)"; h="$(head_sha)"; u="$(upstream)"
  tree_state="$(working_dirty && printf dirty || printf clean)"
  printf 'SongChart Delivery Kernel\n'
  printf 'Branch: %s\nHEAD: %s\nWorking tree: %s\n' "$b" "$h" "$tree_state"
  printf 'Lifecycle evidence: unresolved; clean Git state alone never implies CHECKED/CLOSED.\n'
  if [[ -n "$u" ]]; then
    counts="$(git -C "$ROOT" rev-list --left-right --count "HEAD...$u" 2>/dev/null || printf '? ?')"
    printf 'Upstream: %s\nAhead/behind: %s\n' "$u" "$counts"
  else
    printf 'Upstream: none\n'
  fi
  print_registry_summary
}
plan(){
  exec "$ROOT/songchart" impact "$@"
}
check(){
  [[ $# -eq 0 ]] || die 'Usage: ./songchart check'
  exec "$ROOT/songchart" impact --verify
}
close(){
  [[ $# -eq 0 ]] || die 'Usage: ./songchart close'
  exec "$ROOT/songchart" close
}
doctor(){
  exec "$ROOT/songchart" ai doctor "$@"
}
promote(){
  [[ $# -eq 0 ]] || die 'Usage: ./songchart promote'
  working_dirty && die 'Promotion preflight requires a clean working tree.'
  local u counts ahead behind
  u="$(upstream)"; [[ -n "$u" ]] || die 'Promotion preflight requires an upstream branch.'
  counts="$(git -C "$ROOT" rev-list --left-right --count "HEAD...$u")"
  ahead="${counts%%[[:space:]]*}"; behind="${counts##*[[:space:]]}"
  [[ "$ahead" == '0' && "$behind" == '0' ]] || die "Promotion preflight requires upstream sync; ahead/behind=$counts"
  printf '[SongChart promote] exact tree is Git-clean and upstream-synchronized.\n'
  printf 'HEAD=%s\nUPSTREAM=%s\n' "$(head_sha)" "$u"
  printf 'Promotion is not granted locally. Required next authority: canonical evidence and GitHub PR CI on this exact SHA before merge.\n'
}
cmd="${1:-status}"; shift || true
case "$cmd" in
  status) status "$@" ;;
  plan) plan "$@" ;;
  check) check "$@" ;;
  close) close "$@" ;;
  promote) promote "$@" ;;
  doctor) doctor "$@" ;;
  *) die 'Usage: delivery-kernel.sh [status|plan|check|close|promote|doctor]' ;;
esac

#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT="${SONGCHART_DEV_PROJECT:-songchart-dev}"
QUIET=false

if [[ "${1:-}" == "--quiet" ]]; then
  QUIET=true
elif [[ $# -gt 0 ]]; then
  printf 'Usage: bash scripts/recover-docker-dev-runtime.sh [--quiet]\n' >&2
  exit 2
fi

command -v docker >/dev/null 2>&1 || { printf 'Docker CLI is required.\n' >&2; exit 1; }
docker version >/dev/null 2>&1 || { printf 'Docker Engine is not reachable.\n' >&2; exit 1; }

mapfile -t containers < <(docker ps -aq --filter "label=com.docker.compose.project=${PROJECT}" 2>/dev/null || true)
recovered=0

for id in "${containers[@]}"; do
  [[ -n "$id" ]] || continue
  state="$(docker inspect -f '{{.State.Status}}' "$id" 2>/dev/null || printf 'unknown')"
  [[ "$state" == "running" ]] && continue

  name="$(docker inspect -f '{{.Name}}' "$id" 2>/dev/null | sed 's#^/##' || true)"
  [[ -n "$name" ]] || name="$id"

  if [[ "$QUIET" != true ]]; then
    printf '[SongChart dev recovery] Recreating non-running container %s (state=%s); named volumes are preserved.\n' "$name" "$state"
  fi

  if ! docker rm -f "$id" >/dev/null 2>&1; then
    printf '[SongChart dev recovery] Could not remove stale container %s. Restart the Docker daemon/Codespace and rerun; do not delete volumes.\n' "$name" >&2
    exit 1
  fi
  recovered=$((recovered + 1))
done

if [[ "$QUIET" != true ]]; then
  if [[ "$recovered" -eq 0 ]]; then
    printf '[SongChart dev recovery] No stale SongChart development containers found.\n'
  else
    printf '[SongChart dev recovery] Recovered %s container(s). Persistent Docker volumes were not removed.\n' "$recovered"
  fi
fi

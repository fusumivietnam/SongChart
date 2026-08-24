#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

docker compose -f compose.verify.yml down --remove-orphans
docker compose -f compose.verify.yml build verify
docker compose -f compose.verify.yml run --rm verify
docker compose -f compose.verify.yml down --remove-orphans

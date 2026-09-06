#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SONGCHART="$ROOT/songchart"

printf '[SongChart demo] scripts/shared-demo.sh is a compatibility adapter; ./songchart demo is the single operational authority.\n' >&2

cmd="${1:-help}"
shift || true

case "$cmd" in
  setup)
    exec "$SONGCHART" demo setup "$@"
    ;;
  ready)
    "$SONGCHART" demo setup "$@"
    exec "$SONGCHART" demo status
    ;;
  up|down|status|logs|url)
    exec "$SONGCHART" demo "$cmd" "$@"
    ;;
  migrate|seed)
    printf 'SongChart demo: %s is retired from the compatibility adapter. Demo schema/provider preparation is owned by ./songchart demo setup and compose.demo.yml.\n' "$cmd" >&2
    exit 2
    ;;
  help|--help|-h)
    cat <<'EOF'
SongChart demo compatibility adapter
  ./scripts/shared-demo.sh setup|ready|up|down|status|logs|url

Canonical operational surface:
  ./songchart demo setup|up|down|status|logs|url

Legacy migrate/seed subcommands are retired. The current development-demo adapter uses the governed development PostgreSQL authority and the single shared development queue worker; it does not own a second queue or database lifecycle.
EOF
    ;;
  *)
    printf 'SongChart demo: unknown compatibility command [%s]. Use ./songchart demo --help.\n' "$cmd" >&2
    exit 2
    ;;
esac

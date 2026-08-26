#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT="${SONGCHART_DEMO_PROJECT:-songchart-demo}"
PORT="${SONGCHART_DEMO_PORT:-8001}"
COMPOSE=(docker compose -p "$PROJECT" -f "$ROOT/compose.demo.yml")
ENV_FILE="$ROOT/.env.demo"
EXAMPLE="$ROOT/docker/demo/env.example"

fail(){ printf 'SongChart demo: %s\n' "$*" >&2; exit 1; }
require_docker(){ command -v docker >/dev/null 2>&1 || fail 'Docker CLI is required.'; docker version >/dev/null 2>&1 || fail 'Docker Engine is not reachable.'; docker compose version >/dev/null 2>&1 || fail 'Docker Compose v2 plugin is required.'; }

codespaces_mode(){ [[ "${CODESPACES:-false}" == "true" ]]; }
demo_url(){
  if codespaces_mode; then
    [[ -n "${CODESPACE_NAME:-}" ]] || fail 'CODESPACE_NAME is required in GitHub Codespaces.'
    printf 'https://%s-%s.%s\n' "$CODESPACE_NAME" "$PORT" "${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
  else
    printf 'http://127.0.0.1:%s\n' "$PORT"
  fi
}

prepare_env(){
  [[ -f "$EXAMPLE" ]] || fail 'docker/demo/env.example is missing.'
  [[ -f "$ENV_FILE" ]] || cp "$EXAMPLE" "$ENV_FILE"

  if [[ -n "${SONGCHART_DEMO_DATABASE_URL:-}" ]]; then
    python3 - "$ENV_FILE" "$SONGCHART_DEMO_DATABASE_URL" <<'PY'
from pathlib import Path
import re,sys
p=Path(sys.argv[1]); value=sys.argv[2]
t=p.read_text()
t=re.sub(r'(?m)^DB_URL=.*$', 'DB_URL='+value, t)
p.write_text(t)
PY
  fi
  if [[ -n "${SONGCHART_DEMO_APP_KEY:-}" ]]; then
    python3 - "$ENV_FILE" "$SONGCHART_DEMO_APP_KEY" <<'PY'
from pathlib import Path
import re,sys
p=Path(sys.argv[1]); value=sys.argv[2]
t=p.read_text()
t=re.sub(r'(?m)^APP_KEY=.*$', 'APP_KEY='+value, t)
p.write_text(t)
PY
  fi

  local db_url app_key
  db_url="$(grep -E '^DB_URL=' "$ENV_FILE" | cut -d= -f2-)"
  app_key="$(grep -E '^APP_KEY=' "$ENV_FILE" | cut -d= -f2-)"
  [[ -n "$db_url" && "$db_url" != postgresql://USER:* ]] || fail 'Set SONGCHART_DEMO_DATABASE_URL (recommended Codespaces secret) or edit .env.demo with the shared PostgreSQL connection URL.'
  [[ "$db_url" == postgresql://* || "$db_url" == postgres://* ]] || fail 'Shared demo DB_URL must be PostgreSQL.'
  if [[ "$db_url" =~ @(localhost|127\.0\.0\.1|postgres)(:|/) ]]; then fail 'Shared demo DB_URL must not target the local development database.'; fi
  [[ -n "$app_key" ]] || fail 'Set SONGCHART_DEMO_APP_KEY to one stable shared Laravel APP_KEY before using the demo environment.'
}

export SONGCHART_HOST_UID="${SONGCHART_HOST_UID:-$(id -u)}"
export SONGCHART_HOST_GID="${SONGCHART_HOST_GID:-$(id -g)}"
export SONGCHART_DEMO_PORT="$PORT"
export SONGCHART_DEMO_APP_URL="$(demo_url)"

cmd="${1:-help}"; shift || true
require_docker
case "$cmd" in
  setup)
    prepare_env
    "${COMPOSE[@]}" build app queue
    "${COMPOSE[@]}" run --rm --user root app sh -lc "mkdir -p /workspace/vendor /workspace/node_modules /workspace/public/build /workspace/bootstrap/cache /workspace/storage/framework/cache/data /workspace/storage/framework/sessions /workspace/storage/framework/views /workspace/storage/logs /tmp/composer-cache /tmp/npm-cache && chown -R $SONGCHART_HOST_UID:$SONGCHART_HOST_GID /workspace/vendor /workspace/node_modules /workspace/public/build /workspace/bootstrap/cache /workspace/storage /tmp/composer-cache /tmp/npm-cache"
    "${COMPOSE[@]}" run --rm app composer install --no-interaction --prefer-dist --no-progress
    "${COMPOSE[@]}" run --rm app npm ci --no-audit --no-fund
    "${COMPOSE[@]}" run --rm app npm run build
    printf '[SongChart demo] Setup complete. No shared-database migration was run automatically.\n'
    printf 'Next: ./scripts/shared-demo.sh migrate\n' ;;
  migrate)
    prepare_env
    "${COMPOSE[@]}" run --rm app php artisan migrate --force
    printf '[SongChart demo] Shared database migrations applied.\n' ;;
  seed)
    prepare_env
    "${COMPOSE[@]}" run --rm app php artisan db:seed '--class=Database\\Seeders\\ProviderRegistrySeeder' --force
    printf '[SongChart demo] Safe provider registry seed applied.\n' ;;
  up)
    prepare_env
    "${COMPOSE[@]}" up -d redis app queue
    "${COMPOSE[@]}" ps
    printf '\nShared demo: %s\n' "$SONGCHART_DEMO_APP_URL" ;;
  ready)
    prepare_env
    "${COMPOSE[@]}" up -d redis app queue
    "${COMPOSE[@]}" exec app php artisan view:clear
    "${COMPOSE[@]}" ps
    printf '\nShared demo: %s\n' "$SONGCHART_DEMO_APP_URL" ;;
  down) "${COMPOSE[@]}" down --remove-orphans ;;
  status) "${COMPOSE[@]}" ps ;;
  logs) "${COMPOSE[@]}" logs --follow --tail 200 "$@" ;;
  url) printf '%s\n' "$SONGCHART_DEMO_APP_URL" ;;
  help|--help|-h)
    cat <<'EOF'
SongChart shared demo environment
  ./scripts/shared-demo.sh setup
  ./scripts/shared-demo.sh migrate
  ./scripts/shared-demo.sh seed
  ./scripts/shared-demo.sh ready|up|down|status|logs|url

The demo profile uses one remote PostgreSQL database and local ephemeral Redis/queue/app containers.
It never runs migrate:fresh and setup never mutates shared schema automatically.
Recommended secrets: SONGCHART_DEMO_DATABASE_URL and SONGCHART_DEMO_APP_KEY.
EOF
    ;;
  *) fail 'Usage: shared-demo.sh [setup|migrate|seed|ready|up|down|status|logs|url]' ;;
esac

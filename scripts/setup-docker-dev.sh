#!/usr/bin/env bash
set -euo pipefail
DOMAIN="${1:-docker.songchart.test}"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT="${SONGCHART_DEV_PROJECT:-songchart-dev}"
IS_CODESPACES=false
if [[ "${CODESPACES:-false}" == "true" ]]; then
  IS_CODESPACES=true
  [[ -f "$ROOT/compose.codespaces.yml" ]] || { echo 'compose.codespaces.yml is required in GitHub Codespaces.' >&2; exit 1; }
  [[ -n "${CODESPACE_NAME:-}" ]] || { echo 'CODESPACE_NAME is required in GitHub Codespaces.' >&2; exit 1; }
  export SONGCHART_CODESPACES_APP_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
  COMPOSE=(docker compose -p "$PROJECT" -f "$ROOT/compose.dev.yml" -f "$ROOT/compose.codespaces.yml")
else
  COMPOSE=(docker compose -p "$PROJECT" -f "$ROOT/compose.dev.yml")
fi
cd "$ROOT"
command -v docker >/dev/null || { echo 'Docker CLI required' >&2; exit 1; }
docker version >/dev/null || { echo 'Docker Engine not reachable' >&2; exit 1; }
docker compose version >/dev/null || { echo 'Docker Compose v2 required' >&2; exit 1; }
[[ -f .env.docker ]] || cp .env.docker.example .env.docker
python3 - .env.docker <<'PY2'
from pathlib import Path
import base64,os,re,sys
p=Path(sys.argv[1]); t=p.read_text()
if re.search(r'(?m)^APP_KEY=\s*$',t):
 t=re.sub(r'(?m)^APP_KEY=\s*$', 'APP_KEY=base64:'+base64.b64encode(os.urandom(32)).decode(), t); p.write_text(t)
PY2
mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
if [[ "$IS_CODESPACES" == false ]]; then
  mkdir -p .certs
  if [[ ! -f ".certs/$DOMAIN.pem" || ! -f ".certs/$DOMAIN-key.pem" ]]; then
   command -v mkcert >/dev/null || { echo 'mkcert is required for local trusted HTTPS. Install it, then rerun ./songchart dev setup' >&2; exit 1; }
   mkcert -install
   mkcert -cert-file ".certs/$DOMAIN.pem" -key-file ".certs/$DOMAIN-key.pem" "$DOMAIN" localhost 127.0.0.1 ::1
  fi
  if ! grep -Eq "^[[:space:]]*127\.0\.0\.1[[:space:]]+$DOMAIN([[:space:]]|$)" /etc/hosts; then printf '127.0.0.1\t%s\n' "$DOMAIN" | sudo tee -a /etc/hosts >/dev/null; fi
fi
"${COMPOSE[@]}" build app

HOST_UID="${SONGCHART_HOST_UID:-$(id -u)}"
HOST_GID="${SONGCHART_HOST_GID:-$(id -g)}"
export SONGCHART_HOST_UID="$HOST_UID"
export SONGCHART_HOST_GID="$HOST_GID"

printf '[SongChart Linux Setup] Preparing writable Docker development paths for UID:GID %s:%s\n' "$HOST_UID" "$HOST_GID"
"${COMPOSE[@]}" run --rm --user root app sh -lc \
  "mkdir -p /workspace/vendor /workspace/node_modules /workspace/public/build /workspace/bootstrap/cache /workspace/storage/framework/cache/data /workspace/storage/framework/sessions /workspace/storage/framework/views /workspace/storage/logs && chown -R $HOST_UID:$HOST_GID /workspace/vendor /workspace/node_modules /workspace/public/build /workspace/bootstrap/cache /workspace/storage"

"${COMPOSE[@]}" up -d postgres redis
printf '[SongChart Linux Setup] Preparing Composer/npm cache ownership for UID:GID %s:%s\n' "$HOST_UID" "$HOST_GID"
"${COMPOSE[@]}" run --rm --user root app sh -lc \
  "mkdir -p /tmp/composer-cache /tmp/npm-cache && chown -R $HOST_UID:$HOST_GID /tmp/composer-cache /tmp/npm-cache"

"${COMPOSE[@]}" run --rm app composer install --no-interaction --prefer-dist --no-progress
"${COMPOSE[@]}" run --rm app npm ci --no-audit --no-fund
"${COMPOSE[@]}" run --rm app npm run build
"${COMPOSE[@]}" run --rm app php artisan migrate --force
"${COMPOSE[@]}" run --rm app php artisan db:seed '--class=Database\Seeders\ProviderRegistrySeeder' --force
if [[ "$IS_CODESPACES" == true ]]; then
  "${COMPOSE[@]}" up -d app queue
else
  "${COMPOSE[@]}" up -d app queue caddy
fi
"${COMPOSE[@]}" ps
if [[ "$IS_CODESPACES" == true ]]; then
  printf '\nReady: %s (private Codespaces forwarded port 8000, project %s)\n' "$SONGCHART_CODESPACES_APP_URL" "$PROJECT"
  printf 'The live demo exists only while this Codespace and the SongChart app service are running.\n'
else
  printf '\nReady: https://%s:8443 (project %s)\n' "$DOMAIN" "$PROJECT"
  printf 'If Windows browser cannot resolve the host, add once to Windows hosts: 127.0.0.1 %s\n' "$DOMAIN"
fi

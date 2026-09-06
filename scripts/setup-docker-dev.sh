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

# Keep host requirements to Docker + basic shell/coreutils only. Do not require Python/PHP/Node on the host.
if grep -Eq '^APP_KEY=[[:space:]]*$' .env.docker; then
  APP_KEY_VALUE="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
  sed -i -E "s|^APP_KEY=[[:space:]]*$|APP_KEY=$APP_KEY_VALUE|" .env.docker
fi
if grep -Eq '^APP_NAME=SongChartWeb[[:space:]]*$' .env.docker; then
  sed -i -E 's|^APP_NAME=SongChartWeb[[:space:]]*$|APP_NAME=SongChart|' .env.docker
fi
if grep -Eq '^SONGCHART_LOCAL_ADMIN_EMAIL=[[:space:]]*$' .env.docker; then
  sed -i -E 's|^SONGCHART_LOCAL_ADMIN_EMAIL=[[:space:]]*$|SONGCHART_LOCAL_ADMIN_EMAIL=admin@songchart.local|' .env.docker
fi

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
  "mkdir -p /workspace/vendor /workspace/node_modules /ms-playwright /workspace/public/build /workspace/bootstrap/cache /workspace/storage/framework/cache/data /workspace/storage/framework/sessions /workspace/storage/framework/views /workspace/storage/logs /tmp/composer-cache /tmp/npm-cache && chown -R $HOST_UID:$HOST_GID /workspace/vendor /workspace/node_modules /ms-playwright /workspace/public/build /workspace/bootstrap/cache /workspace/storage /tmp/composer-cache /tmp/npm-cache"

"${COMPOSE[@]}" up -d postgres redis

COMPOSER_FINGERPRINT="$(cat composer.json composer.lock | sha256sum | awk '{print $1}')"
CURRENT_COMPOSER_FINGERPRINT="$("${COMPOSE[@]}" run --rm -T app sh -lc 'cat /workspace/vendor/.songchart-composer-fingerprint 2>/dev/null || true')"
if [[ "$CURRENT_COMPOSER_FINGERPRINT" != "$COMPOSER_FINGERPRINT" ]] || ! "${COMPOSE[@]}" run --rm -T app test -f /workspace/vendor/autoload.php; then
  printf '[SongChart Linux Setup] Hydrating locked Composer dependencies for fingerprint %s.\n' "$COMPOSER_FINGERPRINT"
  "${COMPOSE[@]}" run --rm app composer install --no-interaction --prefer-dist --no-progress
  "${COMPOSE[@]}" run --rm -T app sh -lc "printf '%s\\n' '$COMPOSER_FINGERPRINT' > /workspace/vendor/.songchart-composer-fingerprint"
else
  printf '[SongChart Linux Setup] Reusing locked Composer dependencies for fingerprint %s.\n' "$COMPOSER_FINGERPRINT"
fi

NPM_FINGERPRINT="$(cat package.json package-lock.json | sha256sum | awk '{print $1}')"
CURRENT_NPM_FINGERPRINT="$("${COMPOSE[@]}" run --rm -T app sh -lc 'cat /workspace/node_modules/.songchart-npm-fingerprint 2>/dev/null || true')"
if [[ "$CURRENT_NPM_FINGERPRINT" != "$NPM_FINGERPRINT" ]] || ! "${COMPOSE[@]}" run --rm -T app test -x /workspace/node_modules/.bin/vite; then
  printf '[SongChart Linux Setup] Hydrating locked npm dependencies for fingerprint %s.\n' "$NPM_FINGERPRINT"
  "${COMPOSE[@]}" run --rm app npm ci --no-audit --no-fund
  "${COMPOSE[@]}" run --rm -T app sh -lc "printf '%s\\n' '$NPM_FINGERPRINT' > /workspace/node_modules/.songchart-npm-fingerprint"
else
  printf '[SongChart Linux Setup] Reusing locked npm dependencies for fingerprint %s.\n' "$NPM_FINGERPRINT"
fi

CURRENT_BROWSER_FINGERPRINT="$("${COMPOSE[@]}" run --rm -T app sh -lc 'cat /ms-playwright/.songchart-playwright-fingerprint 2>/dev/null || true')"
if [[ "$CURRENT_BROWSER_FINGERPRINT" != "$NPM_FINGERPRINT" ]] || ! "${COMPOSE[@]}" run --rm -T app sh -lc 'find /ms-playwright -maxdepth 1 -type d -name "chromium-*" -print -quit | grep -q .'; then
  printf '[SongChart Linux Setup] Hydrating verified Playwright Chromium runtime for fingerprint %s.\n' "$NPM_FINGERPRINT"
  "${COMPOSE[@]}" run --rm app npx playwright install chromium
  "${COMPOSE[@]}" run --rm -T app sh -lc "printf '%s\\n' '$NPM_FINGERPRINT' > /ms-playwright/.songchart-playwright-fingerprint"
else
  printf '[SongChart Linux Setup] Reusing Playwright Chromium runtime for fingerprint %s.\n' "$NPM_FINGERPRINT"
fi

"${COMPOSE[@]}" run --rm app npm run build
"${COMPOSE[@]}" run --rm app php artisan migrate --force
"${COMPOSE[@]}" run --rm app php artisan db:seed '--class=Database\Seeders\ProviderRegistrySeeder' --force

LOCAL_ADMIN_EMAIL="$(grep -m1 '^SONGCHART_LOCAL_ADMIN_EMAIL=' .env.docker | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/' || true)"
if [[ -n "$LOCAL_ADMIN_EMAIL" ]]; then
  LOCAL_ADMIN_NAME="$(grep -m1 '^SONGCHART_LOCAL_ADMIN_NAME=' .env.docker | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/' || true)"
  LOCAL_ADMIN_ROLE="$(grep -m1 '^SONGCHART_LOCAL_ADMIN_ROLE=' .env.docker | cut -d= -f2- | sed -E 's/^"(.*)"$/\1/' || true)"
  [[ -n "$LOCAL_ADMIN_NAME" ]] || LOCAL_ADMIN_NAME='SongChart Admin'
  [[ -n "$LOCAL_ADMIN_ROLE" ]] || LOCAL_ADMIN_ROLE='super_admin'
  printf '[SongChart Linux Setup] Ensuring configured local administrator %s.\n' "$LOCAL_ADMIN_EMAIL"
  "${COMPOSE[@]}" run --rm app php artisan admin:ensure-local "$LOCAL_ADMIN_EMAIL" "--name=$LOCAL_ADMIN_NAME" "--role=$LOCAL_ADMIN_ROLE"
fi

if [[ "$IS_CODESPACES" == true ]]; then
  "${COMPOSE[@]}" up -d app queue
else
  "${COMPOSE[@]}" up -d app queue caddy
fi
"${COMPOSE[@]}" ps
if [[ "$IS_CODESPACES" == true ]]; then
  printf '\nReady: %s (private Codespaces forwarded port 8000, project %s)\n' "$SONGCHART_CODESPACES_APP_URL" "$PROJECT"
  printf 'Development data persists in the songchart_dev_pgdata Docker volume for the life of this Docker/Codespaces environment.\n'
else
  printf '\nReady: https://%s:8443 (project %s)\n' "$DOMAIN" "$PROJECT"
  printf 'If Windows browser cannot resolve the host, add once to Windows hosts: 127.0.0.1 %s\n' "$DOMAIN"
fi

#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="$ROOT/compose.production.yml"
TEMPLATE_FILE="$ROOT/.env.production.example"
ENV_FILE="${SONGCHART_PROD_ENV_FILE:-$ROOT/.env.production}"
BACKUP_DIR="${SONGCHART_PROD_BACKUP_DIR:-$ROOT/.songchart-backups/production}"
NO_INTERACTION=false
PROFILE=""

fail(){ printf 'SongChart production: %s\n' "$*" >&2; exit 1; }
info(){ printf '[SongChart prod] %s\n' "$*"; }
warn(){ printf '[SongChart prod] WARNING: %s\n' "$*" >&2; }
require_docker(){
  command -v docker >/dev/null 2>&1 || fail 'Docker CLI is required.'
  docker version >/dev/null 2>&1 || fail 'Docker Engine is not reachable.'
  docker compose version >/dev/null 2>&1 || fail 'Docker Compose v2 is required.'
}
env_value(){
  local key="$1"
  [[ -f "$ENV_FILE" ]] || return 0
  sed -n -E "s/^${key}=//p" "$ENV_FILE" | tail -n1 | sed -E 's/^"(.*)"$/\1/'
}
env_set(){
  local key="$1" value="$2" escaped
  [[ "$value" != *$'\n'* && "$value" != *$'\r'* ]] || fail "$key may not contain a newline."
  escaped="${value//\\/\\\\}"
  escaped="${escaped//&/\\&}"
  escaped="${escaped//|/\\|}"
  if grep -q -E "^${key}=" "$ENV_FILE"; then
    sed -i -E "s|^${key}=.*$|${key}=${escaped}|" "$ENV_FILE"
  else
    printf '%s=%s\n' "$key" "$value" >> "$ENV_FILE"
  fi
}
random_secret(){ openssl rand -base64 32 | tr -d '\n'; }
ensure_config(){
  [[ -f "$ENV_FILE" ]] || fail "Production environment is missing: $ENV_FILE. Run: ./songchart prod configure"
  chmod 600 "$ENV_FILE"
}
read_prompt(){
  local label="$1" default="${2:-}" value
  if [[ "$NO_INTERACTION" == true ]]; then
    printf '%s' "$default"
    return
  fi
  if [[ -n "$default" ]]; then
    read -r -p "$label [$default]: " value
    printf '%s' "${value:-$default}"
  else
    read -r -p "$label: " value
    printf '%s' "$value"
  fi
}
read_secret(){
  local label="$1" current="${2:-}" value
  if [[ "$NO_INTERACTION" == true ]]; then
    printf '%s' "$current"
    return
  fi
  if [[ -n "$current" ]]; then
    read -r -s -p "$label [leave blank to keep current]: " value; printf '\n' >&2
    printf '%s' "${value:-$current}"
  else
    read -r -s -p "$label: " value; printf '\n' >&2
    printf '%s' "$value"
  fi
}
compose_init(){
  ensure_config
  local instance db_mode redis_mode
  instance="$(env_value SONGCHART_INSTANCE)"; [[ -n "$instance" ]] || instance='songchart-production'
  db_mode="$(env_value SONGCHART_DATABASE_MODE)"; [[ -n "$db_mode" ]] || db_mode='bundled'
  redis_mode="$(env_value SONGCHART_REDIS_MODE)"; [[ -n "$redis_mode" ]] || redis_mode='bundled'
  export SONGCHART_RUNTIME_ENV_FILE="$ENV_FILE"
  COMPOSE_ARGS=(-p "$instance" --env-file "$ENV_FILE" -f "$COMPOSE_FILE")
  [[ "$db_mode" == bundled ]] && COMPOSE_ARGS+=(--profile bundled-db)
  [[ "$redis_mode" == bundled ]] && COMPOSE_ARGS+=(--profile bundled-redis)
}
compose(){ docker compose "${COMPOSE_ARGS[@]}" "$@"; }
validate_config(){
  ensure_config
  local required=(APP_ENV APP_KEY APP_URL SONGCHART_DOMAIN SONGCHART_DATABASE_MODE DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD SONGCHART_REDIS_MODE REDIS_HOST REDIS_PORT)
  local key value db_mode redis_mode sslmode redis_scheme release_channel
  [[ "$(env_value APP_ENV)" == production ]] || fail 'APP_ENV must be production.'
  [[ "$(env_value APP_DEBUG)" == false ]] || fail 'APP_DEBUG must be false.'
  for key in "${required[@]}"; do
    value="$(env_value "$key")"
    [[ -n "$value" ]] || fail "$key is required."
  done
  db_mode="$(env_value SONGCHART_DATABASE_MODE)"; [[ "$db_mode" == bundled || "$db_mode" == external ]] || fail 'SONGCHART_DATABASE_MODE must be bundled or external.'
  redis_mode="$(env_value SONGCHART_REDIS_MODE)"; [[ "$redis_mode" == bundled || "$redis_mode" == external ]] || fail 'SONGCHART_REDIS_MODE must be bundled or external.'
  [[ "$(env_value DB_CONNECTION)" == pgsql ]] || fail 'Production DB_CONNECTION must be pgsql.'
  [[ "$(env_value QUEUE_CONNECTION)" == redis ]] || fail 'Production QUEUE_CONNECTION must be redis.'
  [[ "$(env_value CACHE_STORE)" == redis ]] || fail 'Production CACHE_STORE must be redis.'

  sslmode="$(env_value DB_SSLMODE)"; sslmode="${sslmode:-prefer}"
  if [[ "$db_mode" == external ]]; then
    case "$sslmode" in require|verify-ca|verify-full) ;; *) fail 'External PostgreSQL requires DB_SSLMODE=require, verify-ca, or verify-full.';; esac
  fi

  redis_scheme="$(env_value REDIS_SCHEME)"; redis_scheme="${redis_scheme:-tcp}"
  if [[ "$redis_mode" == external && "$redis_scheme" != tls ]]; then
    fail 'External Redis requires REDIS_SCHEME=tls.'
  fi

  release_channel="$(env_value SONGCHART_RELEASE_CHANNEL)"; release_channel="${release_channel:-local}"
  [[ "$release_channel" == local || "$release_channel" == accepted-main ]] || fail 'SONGCHART_RELEASE_CHANNEL must be local or accepted-main.'
}
validate_release_provenance(){
  local sha channel accepted main_ref
  sha="$(git -C "$ROOT" rev-parse HEAD 2>/dev/null || true)"
  [[ -n "$sha" ]] || fail 'Production builds require a Git checkout with an exact source SHA.'
  git -C "$ROOT" diff --quiet -- || fail 'Production builds require a clean unstaged working tree.'
  git -C "$ROOT" diff --cached --quiet -- || fail 'Production builds require a clean staged working tree.'

  channel="$(env_value SONGCHART_RELEASE_CHANNEL)"; channel="${channel:-local}"
  if [[ "$channel" == accepted-main ]]; then
    accepted="$(env_value SONGCHART_ACCEPTED_MAIN_SHA)"
    [[ -n "$accepted" && "$accepted" != unknown ]] || fail 'Accepted-main builds require SONGCHART_ACCEPTED_MAIN_SHA.'
    [[ "$accepted" == "$sha" ]] || fail "Accepted-main SHA $accepted does not match checked-out SHA $sha."

    main_ref=''
    if git -C "$ROOT" show-ref --verify --quiet refs/remotes/origin/main; then
      main_ref="$(git -C "$ROOT" rev-parse refs/remotes/origin/main)"
    elif git -C "$ROOT" show-ref --verify --quiet refs/heads/main; then
      main_ref="$(git -C "$ROOT" rev-parse refs/heads/main)"
    fi
    [[ -z "$main_ref" || "$main_ref" == "$sha" ]] || fail 'Accepted-main build does not match the available main authority.'
    info "Accepted-main provenance gate PASSED for $sha."
  else
    info "Local production-like build provenance: $sha. Official release publishing must use SONGCHART_RELEASE_CHANNEL=accepted-main."
  fi
}
configure(){
  [[ -f "$TEMPLATE_FILE" ]] || fail 'Missing .env.production.example.'
  if [[ ! -f "$ENV_FILE" ]]; then
    cp "$TEMPLATE_FILE" "$ENV_FILE"
    chmod 600 "$ENV_FILE"
    info "Created runtime-only configuration: $ENV_FILE"
  fi

  if [[ "$NO_INTERACTION" == true ]]; then
    [[ -n "$(env_value APP_KEY)" ]] || fail 'APP_KEY is required in non-interactive mode.'
    validate_config
    return
  fi

  local profile domain acme http_port https_port db_mode redis_mode public_url first_admin
  profile="$PROFILE"
  if [[ -z "$profile" ]]; then
    printf 'Deployment profile:\n  1) single-host  (bundled PostgreSQL + Redis)\n  2) production   (external PostgreSQL + Redis, recommended)\n  3) custom\n'
    profile="$(read_prompt 'Choose profile' '1')"
    case "$profile" in 1) profile=single-host;; 2) profile=production;; 3) profile=custom;; *) fail 'Unknown profile.';; esac
  fi

  domain="$(read_prompt 'Public domain' "$(env_value SONGCHART_DOMAIN)")"
  [[ -n "$domain" ]] || fail 'Domain is required.'
  acme="$(read_prompt 'ACME contact email (optional)' "$(env_value SONGCHART_ACME_EMAIL)")"
  http_port="$(read_prompt 'Public HTTP port' "$(env_value SONGCHART_HTTP_PORT)")"
  https_port="$(read_prompt 'Public HTTPS port' "$(env_value SONGCHART_HTTPS_PORT)")"
  https_port="${https_port:-443}"
  public_url="https://$domain"
  [[ "$https_port" == 443 ]] || public_url="$public_url:$https_port"
  env_set SONGCHART_DOMAIN "$domain"
  env_set APP_URL "$public_url"
  env_set SESSION_DOMAIN "$domain"
  env_set SONGCHART_ACME_EMAIL "$acme"
  env_set SONGCHART_HTTP_PORT "${http_port:-80}"
  env_set SONGCHART_HTTPS_PORT "$https_port"

  first_admin="$(read_prompt 'First production administrator email' "$(env_value SONGCHART_FIRST_ADMIN_EMAIL)")"
  [[ -n "$first_admin" ]] || fail 'A first production administrator email is required.'
  env_set SONGCHART_FIRST_ADMIN_EMAIL "$first_admin"

  case "$profile" in
    single-host) db_mode=bundled; redis_mode=bundled ;;
    production) db_mode=external; redis_mode=external ;;
    custom)
      db_mode="$(read_prompt 'Database mode (bundled/external)' "$(env_value SONGCHART_DATABASE_MODE)")"
      redis_mode="$(read_prompt 'Redis mode (bundled/external)' "$(env_value SONGCHART_REDIS_MODE)")"
      ;;
    *) fail "Unknown profile: $profile" ;;
  esac
  env_set SONGCHART_DATABASE_MODE "$db_mode"
  env_set SONGCHART_REDIS_MODE "$redis_mode"

  local db_host db_port db_name db_user db_password db_sslmode
  if [[ "$db_mode" == bundled ]]; then
    db_host=postgres; db_port=5432; db_sslmode=prefer
  else
    db_host="$(read_prompt 'PostgreSQL host' "$(env_value DB_HOST)")"
    db_port="$(read_prompt 'PostgreSQL port' "$(env_value DB_PORT)")"
    db_sslmode="$(read_prompt 'PostgreSQL SSL mode' "$(env_value DB_SSLMODE)")"
    db_sslmode="${db_sslmode:-require}"
  fi
  db_name="$(read_prompt 'PostgreSQL database' "$(env_value DB_DATABASE)")"
  db_user="$(read_prompt 'PostgreSQL username' "$(env_value DB_USERNAME)")"
  db_password="$(read_secret 'PostgreSQL password' "$(env_value DB_PASSWORD)")"
  [[ -n "$db_password" ]] || db_password="$(random_secret)"
  env_set DB_HOST "$db_host"; env_set DB_PORT "${db_port:-5432}"; env_set DB_DATABASE "$db_name"; env_set DB_USERNAME "$db_user"; env_set DB_PASSWORD "$db_password"; env_set DB_SSLMODE "${db_sslmode:-prefer}"

  local redis_host redis_port redis_password redis_scheme
  if [[ "$redis_mode" == bundled ]]; then
    redis_host=redis; redis_port=6379; redis_scheme=tcp
  else
    redis_host="$(read_prompt 'Redis host' "$(env_value REDIS_HOST)")"
    redis_port="$(read_prompt 'Redis port' "$(env_value REDIS_PORT)")"
    redis_scheme="$(read_prompt 'Redis transport scheme' "$(env_value REDIS_SCHEME)")"
    redis_scheme="${redis_scheme:-tls}"
  fi
  redis_password="$(read_secret 'Redis password (blank allowed for external service if policy permits)' "$(env_value REDIS_PASSWORD)")"
  [[ "$redis_mode" == external || -n "$redis_password" ]] || redis_password="$(random_secret)"
  env_set REDIS_SCHEME "$redis_scheme"; env_set REDIS_QUEUE_SCHEME "$redis_scheme"; env_set REDIS_HOST "$redis_host"; env_set REDIS_PORT "${redis_port:-6379}"; env_set REDIS_PASSWORD "$redis_password"

  if [[ -z "$(env_value APP_KEY)" ]]; then
    env_set APP_KEY "base64:$(random_secret)"
  fi
  validate_config
  info 'Configuration validated. Secrets remain only in the runtime environment file.'
}
set_build_metadata(){
  local sha tag built
  sha="$(git -C "$ROOT" rev-parse HEAD 2>/dev/null || printf unknown)"
  tag="${sha:0:12}"; [[ "$sha" == unknown ]] && tag=local
  built="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  env_set SONGCHART_RELEASE_SHA "$sha"
  env_set SONGCHART_IMAGE_TAG "$tag"
  env_set SONGCHART_BUILD_DATE "$built"
}
build(){
  require_docker; validate_config; validate_release_provenance; set_build_metadata; compose_init
  info "Building immutable application artifact for $(env_value SONGCHART_RELEASE_SHA)."
  compose build app edge
}
start_dependencies(){
  local services=() db_mode redis_mode
  db_mode="$(env_value SONGCHART_DATABASE_MODE)"; redis_mode="$(env_value SONGCHART_REDIS_MODE)"
  [[ "$db_mode" == bundled ]] && services+=(postgres)
  [[ "$redis_mode" == bundled ]] && services+=(redis)
  if (( ${#services[@]} > 0 )); then
    info "Starting bundled dependencies: ${services[*]}"
    compose up -d --wait "${services[@]}"
  fi
}
run_connectivity_checks(){
  info 'Checking Laravel-owned production runtime connectivity.'
  compose run --rm app php artisan songchart:production-runtime-check --no-ansi
}
run_host_preflight(){
  local min_mem min_disk mem_mb disk_mb domain
  min_mem="$(env_value SONGCHART_MIN_MEMORY_MB)"; min_mem="${min_mem:-2048}"
  min_disk="$(env_value SONGCHART_MIN_DISK_MB)"; min_disk="${min_disk:-4096}"

  if [[ -r /proc/meminfo ]]; then
    mem_mb="$(awk '/MemTotal:/ {print int($2/1024)}' /proc/meminfo)"
    [[ "$mem_mb" =~ ^[0-9]+$ ]] || mem_mb=0
    (( mem_mb >= min_mem )) || fail "Host memory ${mem_mb}MB is below SONGCHART_MIN_MEMORY_MB=${min_mem}."
    info "Host memory preflight: ${mem_mb}MB."
  fi

  disk_mb="$(df -Pk "$ROOT" | awk 'NR==2 {print int($4/1024)}')"
  [[ "$disk_mb" =~ ^[0-9]+$ ]] || disk_mb=0
  (( disk_mb >= min_disk )) || fail "Free disk ${disk_mb}MB is below SONGCHART_MIN_DISK_MB=${min_disk}."
  info "Host disk preflight: ${disk_mb}MB free."

  domain="$(env_value SONGCHART_DOMAIN)"
  if command -v getent >/dev/null 2>&1; then
    getent ahosts "$domain" >/dev/null 2>&1 || warn "DNS does not currently resolve $domain. Automatic HTTPS will not succeed until public DNS is correct."
  fi
}
doctor(){
  require_docker; validate_config; compose_init
  run_host_preflight
  info 'Validating production Compose model.'
  compose config --quiet
  if [[ -n "$(compose images -q app 2>/dev/null || true)" ]]; then
    start_dependencies
    run_connectivity_checks
  else
    info 'Application image is not built yet; connectivity checks deferred until build/install.'
  fi
  info 'Doctor PASSED.'
}
graceful_horizon_terminate(){
  if [[ "$(compose ps --status running --services 2>/dev/null | grep -x queue || true)" == queue ]]; then
    info 'Gracefully terminating the running Horizon master before artifact replacement.'
    compose exec -T queue php artisan horizon:terminate --no-ansi
  fi
}
up(){
  require_docker; validate_config; compose_init; start_dependencies
  graceful_horizon_terminate
  compose up -d app queue scheduler edge
  compose ps
}
down(){
  require_docker; compose_init
  info 'Stopping application processes; durable volumes are preserved.'
  compose down --remove-orphans
}
status(){
  require_docker; compose_init
  compose ps
  if [[ "$(compose ps --status running --services 2>/dev/null | grep -x app || true)" == app ]]; then
    compose exec -T app php artisan about --only=environment --no-ansi || true
  fi
  if [[ "$(compose ps --status running --services 2>/dev/null | grep -x queue || true)" == queue ]]; then
    compose exec -T queue php artisan horizon:status --no-ansi || true
  fi
}
backup(){
  require_docker; validate_config; compose_init
  mkdir -p "$BACKUP_DIR"; chmod 700 "$BACKUP_DIR"
  local stamp target latest mode host port db user password sslmode retention mirror
  stamp="$(date -u +%Y%m%dT%H%M%SZ)"
  target="$BACKUP_DIR/songchart-production-$stamp.dump"
  latest="$BACKUP_DIR/songchart-production-latest.dump"
  mode="$(env_value SONGCHART_DATABASE_MODE)"
  db="$(env_value DB_DATABASE)"; user="$(env_value DB_USERNAME)"; password="$(env_value DB_PASSWORD)"
  host="$(env_value DB_HOST)"; port="$(env_value DB_PORT)"; sslmode="$(env_value DB_SSLMODE)"
  info "Creating PostgreSQL custom-format backup: $target"
  if [[ "$mode" == bundled ]]; then
    start_dependencies
    compose exec -T postgres pg_dump -U "$user" -d "$db" -Fc > "$target"
  else
    docker run --rm \
      -e PGPASSWORD="$password" -e PGSSLMODE="${sslmode:-require}" \
      postgres:18.4-bookworm \
      pg_dump -h "$host" -p "$port" -U "$user" -d "$db" -Fc > "$target"
  fi
  test -s "$target" || fail 'Backup file is empty.'
  chmod 600 "$target"
  cp "$target" "$latest"; chmod 600 "$latest"
  retention="$(env_value SONGCHART_BACKUP_RETENTION_COUNT)"; retention="${retention:-14}"
  find "$BACKUP_DIR" -maxdepth 1 -type f -name 'songchart-production-*.dump' -printf '%T@ %p\n' | sort -nr | awk -v keep="$retention" 'NR>keep {sub(/^[^ ]+ /, ""); print}' | xargs -r rm -f

  mirror="$(env_value SONGCHART_BACKUP_MIRROR_DIR)"
  if [[ -n "$mirror" ]]; then
    mkdir -p "$mirror"; chmod 700 "$mirror" 2>/dev/null || true
    cp "$target" "$mirror/$(basename "$target")"
    cp "$latest" "$mirror/songchart-production-latest.dump"
    chmod 600 "$mirror/$(basename "$target")" "$mirror/songchart-production-latest.dump" 2>/dev/null || true
    info "Backup mirrored to $mirror. Mount this path on off-host/object-storage sync when durability requires it."
  fi
  info 'Backup complete.'
}
restore_drill(){
  require_docker; validate_config; compose_init
  local dump="${1:-$BACKUP_DIR/songchart-production-latest.dump}"
  [[ -f "$dump" && -s "$dump" ]] || fail "Backup not found or empty: $dump"
  if [[ -z "$(compose images -q app 2>/dev/null || true)" ]]; then build; compose_init; fi

  local suffix network pg_container app_image app_name app_tag
  suffix="$(date -u +%Y%m%d%H%M%S)-$$"
  network="songchart-restore-drill-$suffix"
  pg_container="songchart-restore-pg-$suffix"
  app_name="$(env_value SONGCHART_APP_IMAGE)"; app_name="${app_name:-songchart/app}"
  app_tag="$(env_value SONGCHART_IMAGE_TAG)"; app_tag="${app_tag:-local}"
  app_image="$app_name:$app_tag"
  cleanup_drill(){ docker rm -f "$pg_container" >/dev/null 2>&1 || true; docker network rm "$network" >/dev/null 2>&1 || true; }
  trap cleanup_drill RETURN

  docker network create "$network" >/dev/null
  docker run -d --name "$pg_container" --network "$network" \
    -e POSTGRES_DB=songchart_restore -e POSTGRES_USER=songchart_restore -e POSTGRES_PASSWORD=songchart_restore_only \
    -v "$dump:/backup.dump:ro" postgres:18.4-bookworm >/dev/null

  local ready=false
  for _ in $(seq 1 30); do
    if docker exec "$pg_container" pg_isready -U songchart_restore -d songchart_restore >/dev/null 2>&1; then ready=true; break; fi
    sleep 1
  done
  [[ "$ready" == true ]] || fail 'Isolated restore PostgreSQL did not become ready.'

  info 'Restoring backup into isolated PostgreSQL 18 drill target.'
  docker exec "$pg_container" pg_restore --no-owner --no-privileges -U songchart_restore -d songchart_restore /backup.dump
  docker exec "$pg_container" psql -U songchart_restore -d songchart_restore -v ON_ERROR_STOP=1 -Atqc 'select count(*) from migrations' >/dev/null

  info 'Proving the immutable application image can read the restored schema.'
  docker run --rm --network "$network" --env-file "$ENV_FILE" \
    -e DB_HOST="$pg_container" -e DB_PORT=5432 -e DB_DATABASE=songchart_restore \
    -e DB_USERNAME=songchart_restore -e DB_PASSWORD=songchart_restore_only -e DB_SSLMODE=disable \
    "$app_image" php artisan migrate:status --no-ansi >/dev/null

  cleanup_drill
  trap - RETURN
  info 'Restore drill PASSED.'
}
smoke(){
  ensure_config
  command -v curl >/dev/null 2>&1 || fail 'curl is required for production smoke.'
  local base headers body
  base="$(env_value APP_URL)"; [[ -n "$base" ]] || fail 'APP_URL is required.'
  headers="$(mktemp)"; body="$(mktemp)"
  cleanup_smoke(){ rm -f "$headers" "$body"; }
  trap cleanup_smoke RETURN
  info "Running deployed HTTP smoke against $base/up"
  curl --fail --silent --show-error --location --connect-timeout 10 --max-time 30 -D "$headers" -o "$body" "$base/up"
  grep -Eiq '^x-content-type-options:[[:space:]]*nosniff' "$headers" || fail 'Expected X-Content-Type-Options header is missing.'
  grep -Eiq '^x-frame-options:[[:space:]]*SAMEORIGIN' "$headers" || fail 'Expected X-Frame-Options header is missing.'
  grep -Eiq '^referrer-policy:[[:space:]]*strict-origin-when-cross-origin' "$headers" || fail 'Expected Referrer-Policy header is missing.'
  grep -Eiq '^strict-transport-security:[[:space:]]*max-age=' "$headers" || fail 'Expected Strict-Transport-Security header is missing.'
  if grep -Eiq '^server:' "$headers"; then fail 'Public Server response header must be removed.'; fi
  cleanup_smoke
  trap - RETURN
  info 'Production smoke PASSED.'
}
bootstrap_first_admin(){
  local email
  email="$(env_value SONGCHART_FIRST_ADMIN_EMAIL)"
  [[ -n "$email" ]] || fail 'SONGCHART_FIRST_ADMIN_EMAIL is required before production install.'
  info "Ensuring first production administrator $email."
  if [[ "$NO_INTERACTION" == true ]]; then
    compose run --rm app php artisan admin:create "$email" --require-existing --no-ansi
  else
    compose run --rm app php artisan admin:create "$email" --name='SongChart Admin' --role=super_admin --if-missing --no-ansi
  fi
}
install(){
  require_docker
  if [[ ! -f "$ENV_FILE" || "$NO_INTERACTION" == false ]]; then configure; else validate_config; fi
  [[ -n "$(env_value SONGCHART_FIRST_ADMIN_EMAIL)" ]] || fail 'SONGCHART_FIRST_ADMIN_EMAIL is required before production install.'
  run_host_preflight
  build
  compose_init
  start_dependencies
  run_connectivity_checks
  info 'Applying production migrations.'
  compose run --rm app php artisan migrate --force --no-ansi
  bootstrap_first_admin
  info 'Caching production framework configuration.'
  compose run --rm app php artisan optimize --no-ansi
  up
  info "READY — $(env_value APP_URL)"
  info 'Run ./songchart prod smoke after public DNS/TLS is reachable.'
}
usage(){ cat <<'EOF'
SongChart production control
  ./songchart prod configure [--profile=single-host|production|custom] [--env-file=PATH]
  ./songchart prod install   [--profile=single-host|production|custom] [--env-file=PATH] [--no-interaction]
  ./songchart prod doctor    [--env-file=PATH]
  ./songchart prod build     [--env-file=PATH]
  ./songchart prod up        [--env-file=PATH]
  ./songchart prod down      [--env-file=PATH]
  ./songchart prod status    [--env-file=PATH]
  ./songchart prod smoke     [--env-file=PATH]
  ./songchart prod db backup [--env-file=PATH]
  ./songchart prod db restore-drill [dump-path] [--env-file=PATH]

Production profile requires encrypted external PostgreSQL/Redis transport.
Official release publishing must set SONGCHART_RELEASE_CHANNEL=accepted-main and exact SONGCHART_ACCEPTED_MAIN_SHA.
Single-host keeps durable PostgreSQL/Redis volumes local without exposing their ports.
Backup mirrors may target an off-host mounted path via SONGCHART_BACKUP_MIRROR_DIR.
Restore verification is isolated by default; this CLI does not overwrite the live production database.
EOF
}

action="${1:-help}"; shift || true
subaction=""
positional=()
if [[ "$action" == db ]]; then subaction="${1:-}"; shift || true; fi
for arg in "$@"; do
  case "$arg" in
    --no-interaction) NO_INTERACTION=true ;;
    --profile=*) PROFILE="${arg#*=}" ;;
    --env-file=*) ENV_FILE="${arg#*=}" ;;
    --*) fail "Unknown option: $arg" ;;
    *) positional+=("$arg") ;;
  esac
done

case "$action" in
  configure) configure ;;
  install) install ;;
  doctor) doctor ;;
  build) build ;;
  up) up ;;
  down) down ;;
  status) status ;;
  smoke) smoke ;;
  db)
    case "$subaction" in
      backup) backup ;;
      restore-drill) restore_drill "${positional[0]:-}" ;;
      *) usage; fail 'Usage: ./songchart prod db [backup|restore-drill [dump-path]]' ;;
    esac ;;
  help|--help|-h) usage ;;
  *) usage; fail "Unknown production action: $action" ;;
esac
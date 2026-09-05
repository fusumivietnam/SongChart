#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="$ROOT/compose.production.yml"
TEMPLATE_FILE="$ROOT/.env.production.example"
ENV_FILE="${SONGCHART_PROD_ENV_FILE:-$ROOT/.env.production}"
NO_INTERACTION=false
PROFILE=""

fail(){ printf 'SongChart production: %s\n' "$*" >&2; exit 1; }
info(){ printf '[SongChart prod] %s\n' "$*"; }
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
  local key value mode
  [[ "$(env_value APP_ENV)" == production ]] || fail 'APP_ENV must be production.'
  [[ "$(env_value APP_DEBUG)" == false ]] || fail 'APP_DEBUG must be false.'
  for key in "${required[@]}"; do
    value="$(env_value "$key")"
    [[ -n "$value" ]] || fail "$key is required."
  done
  mode="$(env_value SONGCHART_DATABASE_MODE)"; [[ "$mode" == bundled || "$mode" == external ]] || fail 'SONGCHART_DATABASE_MODE must be bundled or external.'
  mode="$(env_value SONGCHART_REDIS_MODE)"; [[ "$mode" == bundled || "$mode" == external ]] || fail 'SONGCHART_REDIS_MODE must be bundled or external.'
  [[ "$(env_value DB_CONNECTION)" == pgsql ]] || fail 'Production DB_CONNECTION must be pgsql.'
  [[ "$(env_value QUEUE_CONNECTION)" == redis ]] || fail 'Production QUEUE_CONNECTION must be redis.'
  [[ "$(env_value CACHE_STORE)" == redis ]] || fail 'Production CACHE_STORE must be redis.'
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

  local profile domain acme http_port https_port db_mode redis_mode
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
  env_set SONGCHART_DOMAIN "$domain"
  env_set APP_URL "https://$domain"
  env_set SESSION_DOMAIN "$domain"
  env_set SONGCHART_ACME_EMAIL "$acme"
  env_set SONGCHART_HTTP_PORT "${http_port:-80}"
  env_set SONGCHART_HTTPS_PORT "${https_port:-443}"

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
  fi
  db_name="$(read_prompt 'PostgreSQL database' "$(env_value DB_DATABASE)")"
  db_user="$(read_prompt 'PostgreSQL username' "$(env_value DB_USERNAME)")"
  db_password="$(read_secret 'PostgreSQL password' "$(env_value DB_PASSWORD)")"
  [[ -n "$db_password" ]] || db_password="$(random_secret)"
  env_set DB_HOST "$db_host"; env_set DB_PORT "${db_port:-5432}"; env_set DB_DATABASE "$db_name"; env_set DB_USERNAME "$db_user"; env_set DB_PASSWORD "$db_password"; env_set DB_SSLMODE "${db_sslmode:-prefer}"

  local redis_host redis_port redis_password
  if [[ "$redis_mode" == bundled ]]; then
    redis_host=redis; redis_port=6379
  else
    redis_host="$(read_prompt 'Redis host' "$(env_value REDIS_HOST)")"
    redis_port="$(read_prompt 'Redis port' "$(env_value REDIS_PORT)")"
  fi
  redis_password="$(read_secret 'Redis password (blank allowed for external service if policy permits)' "$(env_value REDIS_PASSWORD)")"
  [[ "$redis_mode" == external || -n "$redis_password" ]] || redis_password="$(random_secret)"
  env_set REDIS_HOST "$redis_host"; env_set REDIS_PORT "${redis_port:-6379}"; env_set REDIS_PASSWORD "$redis_password"

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
  require_docker; validate_config; set_build_metadata; compose_init
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
  info 'Checking PostgreSQL authentication/connectivity.'
  compose run --rm app php -r '$dsn="pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE").";sslmode=".(getenv("DB_SSLMODE") ?: "prefer"); $pdo=new PDO($dsn,(string)getenv("DB_USERNAME"),(string)getenv("DB_PASSWORD"),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); $pdo->query("select 1"); echo "PostgreSQL OK\n";'
  info 'Checking Redis authentication/connectivity.'
  compose run --rm app php -r '$r=new Redis(); $r->connect((string)getenv("REDIS_HOST"),(int)getenv("REDIS_PORT"),5); $p=getenv("REDIS_PASSWORD"); if($p!==false && $p!==""){$r->auth($p);} if($r->ping()===false){fwrite(STDERR,"Redis ping failed\n"); exit(1);} echo "Redis OK\n";'
}
doctor(){
  require_docker; validate_config; compose_init
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
up(){
  require_docker; validate_config; compose_init; start_dependencies
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
install(){
  require_docker
  if [[ ! -f "$ENV_FILE" || "$NO_INTERACTION" == false ]]; then configure; else validate_config; fi
  build
  compose_init
  start_dependencies
  run_connectivity_checks
  info 'Applying production migrations.'
  compose run --rm app php artisan migrate --force --no-ansi
  info 'Caching production framework configuration.'
  compose run --rm app php artisan optimize --no-ansi
  up
  info "READY — https://$(env_value SONGCHART_DOMAIN)"
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

Production profile recommends external PostgreSQL 18 and external Redis.
Single-host keeps durable PostgreSQL/Redis volumes local without exposing their ports.
EOF
}

action="${1:-help}"; shift || true
for arg in "$@"; do
  case "$arg" in
    --no-interaction) NO_INTERACTION=true ;;
    --profile=*) PROFILE="${arg#*=}" ;;
    --env-file=*) ENV_FILE="${arg#*=}" ;;
    *) fail "Unknown option: $arg" ;;
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
  help|--help|-h) usage ;;
  *) usage; fail "Unknown production action: $action" ;;
esac

# Production Topology

Status: Stage 19.0.1 inventory authority for the supported first-release runtime shape. This document describes topology and gaps only; later Stage 19 slices own implementation details.

## Accepted baseline

- Stage 19 starts from accepted `main` merge `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Current development runtime is Docker-first through `compose.dev.yml`.
- PostgreSQL 18 remains the release-authoritative database.
- Redis is already used for queue/cache coordination.
- Laravel `/up` is the existing application health endpoint.
- Existing authorization, audit, provider credential/rate and canonical-identity boundaries are unchanged by production topology work.

## Current runtime inventory

### Web

Current development web runtime:

- image source: `docker/verify/Dockerfile`;
- PHP baseline: PHP 8.5 CLI on Debian Bookworm;
- development process: PHP built-in server on port 8000;
- source, vendor and node_modules are bind/named-volume mounted;
- health check calls `http://127.0.0.1:8000/up`;
- Caddy terminates local TLS and reverse-proxies to `app:8000`.

Production conclusion: the development PHP built-in server and bind-mounted source tree are not supported production primitives.

### Queue

Current development queue runtime is already a separate long-running process using the same application code/image surface:

```text
php artisan queue:work redis
  --queue=critical,discovery-projections,provider-health,provider-imports,provider-normalization,notifications,default
  --sleep=1
  --tries=3
  --timeout=150
```

Production conclusion: preserve an independent queue lifecycle and existing queue taxonomy. Stage 19.0.3 owns the final production worker command/restart model and Horizon decision.

### Scheduler

`routes/console.php` defines scheduled provider health checks, Discovery rebuilds and Horizon snapshots where Horizon is installed. The development Compose stack does not run an independent scheduler process.

Production conclusion: production requires one independently managed scheduler process using the same immutable application image. Stage 19.0.3 owns the concrete lifecycle.

### PostgreSQL

Current Docker development runtime uses `postgres:18.4-bookworm` with a persistent volume and health check through `pg_isready`.

Production conclusion:

- PostgreSQL major 18 is mandatory;
- database storage is stateful and must not live inside the application image;
- production credentials must be injected, not copied from development defaults;
- backup/restore lifecycle is a separate required responsibility owned by Stage 19.0.5.

### Redis

Current Docker development runtime uses `redis:7.4-alpine` with persistent storage and a `redis-cli ping` health check. Redis coordinates queue/cache/runtime state.

Production conclusion:

- Redis is a required runtime dependency for the first release while Redis queues are enabled;
- Redis must be independently restartable from web/worker processes;
- production authentication/network exposure must fail closed and is owned by the environment/security slices;
- Redis is not a substitute for PostgreSQL durability.

### TLS / edge

Current development edge uses Caddy 2.11.3 with local mkcert certificates and HTTP-to-HTTPS redirect.

Production conclusion: Caddy remains the selected first-release reverse-proxy/TLS primitive unless later implementation evidence proves a blocker. Production must use real ACME/provider-managed certificates rather than tracked/local development certificate files.

### Observability

The repository already contains Laravel Pulse configuration and optional Sentry/PostHog environment inputs. `/up` provides application liveness/readiness evidence at the HTTP layer.

Production conclusion: reuse existing observability primitives first. Stage 19.0.4 owns actionable health/alert definitions and must not expose secrets.

### Release/CI

GitHub Actions already provides deterministic PR verification. Canonical release packaging remains post-canonical and must originate from an accepted exact `main` tree.

Production conclusion: deployment must consume a closed immutable artifact/image rather than rebuild from an unverified working tree on the server.

## Supported first-release process topology

The minimal supported runtime topology is:

```text
INTERNET
   |
   v
CADDY / TLS EDGE
   |
   v
WEB PROCESS ---------------> POSTGRESQL 18
   |                              ^
   |                              |
   +-----------> REDIS <----------+
                    ^
                    |
             QUEUE WORKER(S)
                    ^
                    |
                SCHEDULER

All application processes use the same immutable SongChart application image/artifact.
```

Required independent lifecycle units:

1. `edge` — Caddy TLS/reverse proxy;
2. `web` — production PHP web runtime;
3. `queue` — Laravel queue worker/Horizon runtime;
4. `scheduler` — Laravel scheduler runtime;
5. `postgres` — PostgreSQL 18 stateful service or compatible managed PostgreSQL 18 service;
6. `redis` — Redis stateful/runtime service or compatible managed Redis service.

The first release may place these processes on one host for operational simplicity, but process boundaries must remain explicit so web, worker and scheduler can restart independently. Host count is not an application invariant.

## Immutable application artifact requirements

A production application image/artifact must eventually:

- contain application source at the accepted exact commit;
- install Composer dependencies without development packages;
- build frontend assets before runtime;
- avoid bind-mounting repository source, `vendor` or `node_modules` from the host;
- run with `APP_ENV=production` and `APP_DEBUG=false`;
- support web, queue and scheduler entrypoints from the same artifact;
- expose only required runtime files/ports;
- contain no real environment secrets;
- be traceable to the accepted Git commit used for candidate/canonical/PR closure.

Concrete image implementation belongs to later Stage 19 slices after the environment contract is hardened.

## Production environment inventory

### Required application identity/runtime

- `APP_KEY` — secret, mandatory;
- `APP_ENV=production` — non-secret, mandatory;
- `APP_DEBUG=false` — non-secret, mandatory;
- `APP_URL` — public HTTPS canonical origin, mandatory;
- `APP_TIMEZONE`, locale/fallback locale — explicit runtime configuration;
- logging channel/level — production values must not default to debug.

### Required PostgreSQL

- `DB_CONNECTION=pgsql`;
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`;
- `DB_PASSWORD` — secret, mandatory where password auth is used.

Development credentials from `compose.dev.yml` are local-only and forbidden as production defaults.

### Required Redis / async runtime

- `REDIS_CLIENT=phpredis`;
- `REDIS_HOST`, `REDIS_PORT`;
- `REDIS_PASSWORD` when Redis authentication is enabled;
- queue connection/database and retry configuration;
- `QUEUE_CONNECTION=redis` for the accepted first-release async topology.

### Sessions/cache

Current repository defaults vary between local examples and Docker development. Stage 19.0.2 must select explicit production session/cache stores rather than inherit ambiguous local defaults.

### Mail / external services

Mail, analytics, Sentry, Turnstile, Resend and music-provider credentials are capability-dependent. Empty/disabled configuration must remain fail-closed. Enabling a provider requires its existing policy/credential boundaries; production topology does not weaken those checks.

### Provider schedules

- `SONGCHART_PROVIDER_HEALTH_SCHEDULE_ENABLED` controls provider health scheduling;
- `SONGCHART_DISCOVERY_SCHEDULE_ENABLED` controls Discovery scheduled rebuilds;
- queue names/batch/TTL settings remain existing application configuration.

### Observability

Pulse configuration already exists. Sentry/PostHog inputs are optional until Stage 19.0.4 accepts an operator use case and concrete production configuration.

### Development-only settings forbidden in production

The following current development assumptions must not leak into production:

- `APP_ENV=local`;
- `APP_DEBUG=true`;
- `DESIGN_LAB_ENABLED=true` unless explicitly justified;
- `SONGCHART_ADMIN_2FA_MODE=disabled`;
- development database usernames/passwords;
- local mkcert certificate paths;
- PHP built-in web server;
- bind-mounted repository/dependency trees;
- testing database variables as production configuration.

## Explicit gaps after inventory

19.0.1 intentionally does not implement these gaps:

1. no production Dockerfile/application runtime image yet;
2. no production Compose/deployment manifest yet;
3. no production Caddyfile/domain/TLS environment contract yet;
4. no `.env.production.example` or equivalent hardened environment template yet;
5. no fail-closed production configuration validation for critical values yet;
6. no independent production scheduler lifecycle yet;
7. queue/Horizon production process choice and restart policy not yet sealed;
8. no documented/verified PostgreSQL backup + restore procedure yet;
9. no production alerting/health escalation contract yet;
10. no final production smoke/release-tag procedure yet.

These gaps map directly to Stage 19.0.2 through 19.0.6 and final release closure. They are not reasons to create additional infrastructure frameworks in 19.0.1.

## 19.0.1 acceptance

This slice is complete when:

- current runtime assumptions are inventoried;
- the supported minimal process topology is explicit;
- production-relevant environment ownership is categorized;
- development-only assumptions are explicitly rejected for production;
- implementation gaps are mapped to later accepted Stage 19 slices;
- no production deployment implementation has been prematurely introduced.

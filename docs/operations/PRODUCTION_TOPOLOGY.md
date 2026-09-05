# Production Topology

Status: Stage 19 production runtime authority for the supported first-release shape.

## Runtime principles

- Development remains Docker-first through `compose.dev.yml`.
- Production uses `compose.production.yml` and the immutable multi-stage artifact in `docker/production/Dockerfile`.
- PostgreSQL 18 remains release-authoritative durable data.
- Redis owns queue/cache/runtime state and is not a substitute for PostgreSQL durability.
- Web, Horizon and scheduler run as independent processes from the same immutable SongChart application image.
- Caddy is the production TLS/static edge and is built from the same accepted source tree.
- Real secrets live only in the runtime environment/secret boundary; `.env.production` is gitignored and must be mode `0600` when file-backed.
- Production artifacts are traceable to the accepted Git SHA through OCI image metadata and `SONGCHART_RELEASE_SHA`.

## Supported process topology

```text
INTERNET
   |
   v
CADDY / TLS EDGE
   |
   v
PHP-FPM WEB ---------------> POSTGRESQL 18
   |                              ^
   |                              |
   +-----------> REDIS <----------+
                    ^
                    |
                 HORIZON
                    ^
                    |
                SCHEDULER
```

Independent lifecycle units are `edge`, `app`, `queue`, `scheduler`, `postgres` and `redis`. Host count is not an application invariant.

## Installation profiles

### Single-host

The bundled profile starts PostgreSQL 18 and Redis on the same Docker host with named durable volumes. Neither service publishes its database/cache port to the host by default. This profile is appropriate for staging, small installations and simple VPS operation.

### Production

External PostgreSQL 18 and external Redis are recommended for production because their storage, backup, failure and scaling lifecycle can be isolated from disposable application hosts. SongChart code does not distinguish local from remote services beyond the governed environment contract.

### Custom

Operators may select bundled or external PostgreSQL/Redis independently. External boundaries are configurable; internal Compose service names and internal ports remain stable to avoid turning the installer into a general Compose generator.

## Operator-configurable deployment boundary

The production installer owns the following deployment-facing values:

- `SONGCHART_INSTANCE`;
- `SONGCHART_DOMAIN`, `SONGCHART_ACME_EMAIL`;
- `SONGCHART_HTTP_PORT`, `SONGCHART_HTTPS_PORT`;
- `SONGCHART_DATABASE_MODE= bundled|external`;
- PostgreSQL host, port, database, username, password and `DB_SSLMODE`;
- `SONGCHART_REDIS_MODE=bundled|external`;
- Redis host, port, username and password.

Stable internal authority remains:

- service names `edge`, `app`, `queue`, `scheduler`, `postgres`, `redis`;
- PHP-FPM port 9000;
- PostgreSQL internal port 5432;
- Redis internal port 6379;
- PostgreSQL major 18;
- Horizon queue supervision and `config/horizon.php` queue topology;
- Laravel `schedule:work` scheduler lifecycle.

## Immutable application artifact

`docker/production/Dockerfile` builds production assets in stages:

1. locked Node 24 dependencies build the Vite frontend;
2. locked Composer dependencies install with `--no-dev`;
3. PHP 8.5 FPM receives source, optimized vendor autoloading and built frontend assets;
4. the Caddy edge target receives only the public tree and governed Caddy configuration.

Production must not use the PHP built-in development server, host bind-mounted source, host `vendor`, or host `node_modules`.

The `app`, `queue` and `scheduler` services share one application image. Commands are intentionally thin:

```text
app       -> php-fpm -F
queue     -> php artisan horizon
scheduler -> php artisan schedule:work
```

Before replacing a running artifact, terminate Horizon gracefully with `php artisan horizon:terminate` and allow the process manager to start Horizon from the newly accepted image.

## Edge / TLS

Caddy owns public HTTP/HTTPS ports, automatic TLS and static public assets. PostgreSQL and Redis are private runtime dependencies and are not publicly exposed by the first-release Compose authority.

Production domain and public host ports are configurable. Container ports and service identities remain fixed.

## Environment and secrets

`.env.production.example` documents non-secret defaults and required keys. `./songchart prod configure` creates the runtime-only `.env.production`; `./songchart prod install --env-file=... --no-interaction` supports secret-manager/deployment automation without requiring an interactive wizard.

Mandatory safety invariants include:

- `APP_ENV=production`;
- `APP_DEBUG=false`;
- HTTPS canonical `APP_URL`;
- PostgreSQL connection;
- Redis queue/cache connection;
- secure sessions;
- required Admin 2FA;
- Design Lab disabled;
- no tracked real secrets.

## Production operations CLI

The supported operator surface is:

```text
./songchart prod configure
./songchart prod install
./songchart prod doctor
./songchart prod build
./songchart prod up
./songchart prod down
./songchart prod status
```

The CLI delegates to Docker Compose and Laravel; it does not replace either runtime owner. Normal `down` never deletes durable volumes.

## Queue / scheduler / observability

Laravel Horizon owns Redis queue supervision, worker lifecycle, queue throughput/wait visibility and failed-job operational context. Laravel Pulse owns broader application/runtime observability. SongChart Admin may summarize their state but must not duplicate their dashboards.

`/up` remains application HTTP health evidence. Production diagnostics must redact credentials, tokens, cookies and private configuration.

## Data durability and growth

Application images are disposable. PostgreSQL is durable. Redis is replaceable runtime state. Large media/blob payloads should remain externalizable rather than making PostgreSQL an object store.

External PostgreSQL is recommended as data size and operational criticality grow. Moving from bundled to external PostgreSQL is an operational migration, not an application/domain rewrite, because the application consumes only the configured PostgreSQL boundary.

Stage 19.0.5 owns backup retention and verified restore evidence. A backup is not accepted until an isolated restore proves a usable application state.

## Remaining Stage 19 closure

Production artifact and installer authority now exist. Remaining release work is bounded to:

1. real-browser desktop/mobile critical smoke coverage;
2. verifier reduction, exact-main provenance and CI failure classification;
3. provider transport/typed-data package admission decisions where they produce net reduction;
4. PostgreSQL backup + isolated restore drill;
5. security review and deployed production smoke;
6. first release package/tag from an accepted exact `main` tree.

No additional deployment framework is required unless the accepted first-release topology proves a concrete blocker.

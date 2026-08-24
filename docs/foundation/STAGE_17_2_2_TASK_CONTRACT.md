# Stage 17.2.2 — Docker HTTP Environment Propagation Corrective

Status: implementation candidate.

## Goal

Ensure the Docker development HTTP process uses the same Docker-native database, Redis, session, and provider environment that CLI commands already resolve correctly. Remove Laravel `ServeCommand` as an unnecessary process-spawning layer from the Docker app service.

## Non-goals

- no application-domain behavior change;
- no PostgreSQL or Redis volume reset;
- no Caddy routing change;
- no production web-server decision;
- no queue topology change;
- no MusicBrainz contract change.

## Acceptance criteria

- Docker app service launches PHP's built-in development server directly;
- Laravel's framework router script remains the request router so non-file application routes continue to resolve;
- Caddy continues to proxy only to `app:8000`;
- app process receives Docker `DB_HOST=postgres` / `DB_DATABASE=songchart_docker` environment directly;
- architecture/static verification rejects reintroduction of `php artisan serve` in `compose.dev.yml`;
- existing `.env`, `.env.docker`, Docker volumes, and user data remain untouched.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `compose.dev.yml`
- `docker/dev/Caddyfile`
- `docker/verify/Dockerfile`

### Installed versions

No dependency changes. Runtime remains PHP 8.5, Laravel 13, PostgreSQL 18.4, Redis 7.4, and Caddy 2.11.3 in the existing Docker development profile.

### Official external sources

- Laravel framework `ServeCommand` source/issues: https://github.com/laravel/framework
- PHP command-line / built-in development server manual: https://www.php.net/manual/en/features.commandline.webserver.php

The target incident demonstrated that a fresh CLI process inside the app container resolved `postgres/songchart_docker`, while HTTP requests served by the long-running process still resolved host-local defaults. The corrective removes the Laravel ServeCommand child-process layer and launches the same PHP development server directly from Docker Compose.

### Native capability assessment

PHP natively provides the development web server. Laravel already ships the framework router script used by its development server. Docker Compose natively injects service environment into the launched process. No package or custom server abstraction is required.

### Custom implementation justification

The only custom repository decision is orchestration configuration: Compose invokes PHP's native server directly with Laravel's existing router script. This reduces process layers and makes environment ownership explicit.

## Security, authorization, and data impact

No authorization or schema changes. Caddy remains the only host-published HTTPS entry point and continues to proxy to the internal app service. No database or cache data is deleted.

## Tests and verification

- PHP syntax sweep;
- `tests/Architecture/DockerLocalDevelopmentTest.php`;
- `scripts/verify-docker-local-development.php`;
- repository-state/documentation/official-source verifiers;
- target runtime assertion that HTTP `/` and `/development/status` resolve Docker PostgreSQL rather than `127.0.0.1`;
- final target closure through `verify-songchart.bat`.

## Rollback

Restore the previous app command in `compose.dev.yml`. No database migration rollback is required.

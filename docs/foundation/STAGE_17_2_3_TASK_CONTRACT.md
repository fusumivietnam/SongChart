# Stage 17.2.3 — Docker PHP Router Working-Directory Corrective

Status: implementation candidate.

## Goal

Correct the Docker development HTTP command introduced in Stage 17.2.2 so Laravel's framework development router resolves `public/index.php` from the public document root while preserving `/workspace` as the service working directory for Composer, Artisan, npm, and setup commands.

## Non-goals

- no application-domain behavior change;
- no database, Redis, queue, provider, or schema change;
- no Docker volume reset;
- no Caddy routing change;
- no production web-server decision.

## Acceptance criteria

- Docker app service keeps `working_dir: /workspace`;
- HTTP command changes directory to `/workspace/public` only for the long-running PHP server process;
- Laravel's framework `server.php` router is invoked relative to the public working directory;
- `/`, `/development/status`, `/admin`, Livewire, and application routes resolve through `public/index.php`;
- static assets remain served by PHP's development server;
- Docker setup commands that run Composer, npm, Artisan, or migrations continue from `/workspace`;
- architecture/static verification rejects both `php artisan serve` and the broken Stage 17.2.2 root-working-directory command.

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

- PHP built-in web server manual: https://www.php.net/manual/en/features.commandline.webserver.php
- Laravel framework repository / development router source: https://github.com/laravel/framework

The PHP manual defines the optional command-line PHP file as a router script executed for each request. Laravel's framework router resolves the front controller relative to the process working directory. Stage 17.2.2 ran that router from `/workspace`, causing it to require `/workspace/index.php`; SongChart's front controller is `/workspace/public/index.php`.

### Native capability assessment

PHP's built-in development server and Laravel's shipped framework router already provide the required local HTTP behavior. Docker Compose can launch a shell command that changes directory only for the server process. No custom router or additional package is necessary.

### Custom implementation justification

The repository-specific orchestration command performs `cd public` before starting PHP and uses `exec` so the PHP server is the container's long-running process. Service-level `working_dir` remains `/workspace` because setup and one-off Compose commands depend on the project root.

## Security, authorization, and data impact

No authorization, schema, secret, or persistent-data changes. Caddy remains the only host-published HTTPS entry point. Existing `.env`, `.env.docker`, database volumes, and Redis volumes remain untouched.

## Tests and verification

- `tests/Architecture/DockerLocalDevelopmentTest.php`;
- `scripts/verify-docker-local-development.php`;
- repository-state/documentation/official-source verifiers;
- target `docker compose -f compose.dev.yml up -d --force-recreate app caddy`;
- target browser checks for `/` and `/development/status`;
- final target closure via `verify-songchart.bat`.

## Rollback

Restore the Stage 17.2.2 app command in `compose.dev.yml`. No migration or data rollback is required.

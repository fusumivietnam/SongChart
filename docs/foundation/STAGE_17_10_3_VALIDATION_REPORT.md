# Stage 17.10.3 Validation Report

## Stage

17.10.3 — Linux-first CLI + Stable Docker Identity

## Candidate

v1

## Scope validated

- Linux/WSL source tree is the primary development working tree.
- ./songchart is the primary host CLI.
- Development Compose identity is stable as songchart-dev.
- Canonical verification Compose identity is stable as songchart-verify.
- Windows PowerShell/BAT entrypoints remain compatibility-only.
- Development app and queue services run with the host WSL UID/GID.
- Composer and npm caches have explicit writable paths for the non-root development user.
- ProviderRegistrySeeder invocation preserves its PHP namespace.
- Development database volumes are not deleted or recreated by this corrective workflow.

## Runtime evidence

The Linux-first CLI successfully executed Laravel through the development container.

Observed runtime:

- Laravel 13.25.0
- PHP 8.5.9
- Composer 2.10.2
- Environment: local
- Database: PostgreSQL
- Cache: Redis
- Queue: Redis
- Session: database
- Application URL: docker.songchart.test:8443

## Docker identity

Development Compose project:

songchart-dev

Canonical verification Compose project:

songchart-verify

Development app container identity observed:

uid=1000 gid=1000

## Cache and ownership contract

Development services use:

- HOME=/tmp
- NPM_CONFIG_CACHE=/tmp/npm-cache
- COMPOSER_CACHE_DIR=/tmp/composer-cache

Dependency and cache ownership is prepared for the WSL host UID/GID.

## HTTPS development certificate

mkcert owns the local development certificate.

When the browser runs on Windows, the WSL mkcert root CA must also be trusted by the Windows certificate store.

## Verification status

- Linux runtime smoke: PASS
- Linux CLI smoke: PASS
- Candidate metadata synchronized to Stage 17.10.3
- Candidate closure: pending
- Canonical closure: pending

## Safety

- No verification gate was weakened.
- No PostgreSQL development volume was deleted or recreated.
- No .env or .env.docker file is made repository authority.
- Private certificate keys remain local-only.

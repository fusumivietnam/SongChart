# Docker Local Development & Trusted HTTPS

## Purpose

This profile provides a browser-accessible Docker development environment while preserving Laragon as an independent local runtime.

It is separate from `compose.verify.yml`. Canonical verification remains the release/candidate authority.

## Default endpoints

- HTTPS: `https://docker.songchart.test:8443`
- HTTP redirect: `http://docker.songchart.test:8080`

Ports 8080/8443 are deliberate. Docker must not compete with Laragon for host ports 80/443 by default.

## Runtime

- PHP 8.5 application image
- PostgreSQL 18.4
- Redis 7.4
- Caddy 2.11.3
- Composer dependencies in `songchart_dev_vendor`
- Node dependencies in `songchart_dev_node_modules`
- persistent Docker development PostgreSQL volume
- source bind-mounted from the repository

## HTTPS trust model

`mkcert` runs on the Windows host. The setup script:

1. verifies Docker Compose and mkcert;
2. creates `.env.docker` from `.env.docker.example`;
3. generates a Docker-local `APP_KEY` when absent;
4. runs `mkcert -install`;
5. generates a certificate for `docker.songchart.test`, `localhost`, `127.0.0.1`, and `::1`;
6. adds `127.0.0.1 docker.songchart.test` to the Windows hosts file when missing;
7. mounts certificate/key read-only into Caddy.

Never commit `.env.docker`, certificate private keys, or the mkcert root CA private key.

## First-time setup

Run from an Administrator PowerShell:

```bat
docker-dev-setup.bat
```

## Normal lifecycle

```bat
docker-dev-up.bat
docker-dev-down.bat
```

Inspect:

```powershell
docker compose -f compose.dev.yml ps
docker compose -f compose.dev.yml logs -f app
docker compose -f compose.dev.yml logs -f caddy
docker compose -f compose.dev.yml logs -f queue
```

## Data lifecycle

`docker-dev-down.bat` removes containers/networks but preserves named volumes.

To intentionally destroy Docker development data and dependency volumes:

```powershell
docker compose -f compose.dev.yml down -v
```

This does not delete Laragon PostgreSQL data.

## Proxy security

Laravel trusts forwarded proxy headers only when both conditions are true:

- `APP_ENV=local`
- `SONGCHART_TRUST_DOCKER_PROXY=true`

Production/staging do not inherit this trust implicitly.

## Frontend

First-time setup runs `npm ci` and `npm run build`. Stage 16.4.3 deliberately uses built assets for the stable HTTPS profile; secure Vite HMR is not required for this stage and may be added separately if its workflow is justified.

## Fast development loop

After applying a source changeset, use:

```powershell
.\songchart.bat dev cycle
```

The cycle is intentionally fail-fast and leaves the normal Docker development stack live:

1. reconcile the persistent PostgreSQL development role password with `compose.dev.yml`;
2. migrate the live development database and clear Laravel caches;
3. recreate app/queue/Caddy;
4. smoke-test both `/up` and `/` over HTTPS;
5. run the focused PostgreSQL feature test (default: `tests/Feature/EntityDetailSystemTest.php`);
6. run full canonical verification in the isolated `songchart-verify` Compose project;
7. smoke-test the live development URL again.

Use `.\songchart.bat dev ready` when only runtime repair/bootstrap and live smoke testing are required. This is non-destructive to the PostgreSQL named volume; it repairs the development role password rather than deleting the volume.

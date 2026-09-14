# Docker Local Development & Trusted HTTPS

## Purpose

This profile is the primary browser-accessible SongChart development runtime for Linux/WSL2. GitHub Codespaces adapts the same repository/runtime contract for remote development. Native Windows Batch/PowerShell and Laragon execution paths are retired.

Development runtime and canonical verification remain separate Compose projects. `compose.dev.yml` owns the live development environment; `compose.verify.yml` owns isolated canonical verification.

## Default endpoint

Outside Codespaces, the development URL is:

```text
https://docker.songchart.test:8443
```

Codespaces derives its forwarded application URL through the `./songchart` runtime adapter.

## Runtime

- PHP 8.5 application image
- PostgreSQL 18.x for local development when local database mode is selected
- Redis 7.4
- Caddy 2.11.3 for the local HTTPS edge
- Composer and Node dependencies stored in governed Docker volumes
- persistent PostgreSQL development data
- source bind-mounted from the repository

## Setup and lifecycle

Use the repository command surface rather than host-specific scripts:

```bash
./songchart dev setup
./songchart dev up
./songchart dev status
./songchart dev down
```

Use `./songchart dev ready` when runtime repair/bootstrap and live smoke verification are required. Use the database subcommands exposed by `./songchart dev db` for governed development backup/restore operations.

The CLI fingerprints `composer.json` + `composer.lock` and `package.json` + `package-lock.json`; locked dependencies are hydrated only when the corresponding fingerprint or runtime cache is missing. Do not run an unconstrained dependency update merely to repair a development container.

## Database modes

Development supports the governed `local` and `remote` PostgreSQL modes declared through `.env.docker`.

- `local`: the development Compose PostgreSQL service owns the development database.
- `remote`: the local PostgreSQL service is not started and the configured durable PostgreSQL authority is used instead.

Database identity/connectivity is checked before destructive backup or restore operations.

## HTTPS trust model

The local HTTPS profile uses Caddy and repository-governed certificate material. Private keys, `.env.docker`, and local certificate authority secrets must never be committed.

Forwarded proxy trust is limited to the development boundary configured by the repository; production/staging must not inherit local proxy trust implicitly.

## Frontend

The development runtime hydrates locked npm dependencies and uses Vite as the single asset compiler. `npm run build` owns deterministic compiled assets; development-server/HMR usage must remain within the existing Vite ownership boundary rather than introducing a second bundler.

## Verification

Use the bounded public command surface:

```bash
./songchart impact --verify
./songchart candidate
./songchart verify
```

Canonical verification runs in its isolated Compose project and must not reuse or destroy the live development PostgreSQL state.

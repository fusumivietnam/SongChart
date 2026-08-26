# Stage 18.1 — Codespaces Development Adapter Task Contract

Status: corrective infrastructure task contract.

## Goal

Make GitHub Codespaces a first-class remote development adapter for SongChart without replacing the Docker-first runtime or local trusted-HTTPS workflow.

## Acceptance criteria

- `CODESPACES=true` is detected automatically by the Linux `songchart` CLI.
- Codespaces uses `compose.dev.yml` plus `compose.codespaces.yml` and the same Docker application/database/queue services as local development.
- Codespaces does not require `mkcert`, local CA installation, `/etc/hosts` mutation, or the Caddy development proxy.
- The application is published only on host loopback port `8000`; GitHub Codespaces owns external HTTPS forwarding and access control.
- `APP_URL` is derived from `CODESPACE_NAME` and `GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN`; session cookies remain secure and host-scoped.
- Opening a Codespace does not auto-start SongChart services. Services start only through `./songchart dev setup`, `./songchart dev ready`, or `./songchart dev up`.
- `./songchart dev url` prints the active development URL for either Codespaces or local Docker development.
- Local Windows/macOS/WSL development keeps the existing Caddy + mkcert contract unchanged.
- Repository verification fails if the Codespaces adapter becomes ungoverned or silently changes its port/no-auto-start contract.

## Cost and lifecycle

- Repository services cannot keep a stopped Codespace alive.
- Automatic compute shutdown is owned by the GitHub Codespaces idle-timeout account setting, not by an application daemon or repository background task.
- Developers may run `./songchart dev down` to stop project containers before the Codespace itself idles or is manually stopped.

## Verification plan

- `php scripts/verify-docker-local-development.php`
- `php scripts/verify-docker-first-development.php`
- `composer stack:verify`
- Codespaces smoke: `./songchart dev setup`, `./songchart dev url`, `./songchart dev ready`, HTTP `/up` through the forwarded URL, `./songchart dev down`.

## Rollback

Remove the Codespaces override and detection paths while preserving `compose.dev.yml`, Caddy/mkcert local HTTPS, and canonical `compose.verify.yml` unchanged.

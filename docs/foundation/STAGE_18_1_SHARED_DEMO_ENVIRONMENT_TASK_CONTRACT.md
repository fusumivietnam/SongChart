# Stage 18.1 — Shared Demo Environment Task Contract

Status: corrective infrastructure task contract.

## Goal

Provide one shared persistent demo dataset across Windows, macOS and GitHub Codespaces while preserving isolated local development and canonical verification databases.

## Architecture

- Development remains `songchart-dev` with its own local PostgreSQL volume.
- Canonical verification remains `songchart-verify` with its isolated verification database.
- Shared demo uses `compose.demo.yml` for app/queue/Redis only and connects to one remote PostgreSQL database through `DB_URL`.
- The demo application is on-demand. In Codespaces it is published on host loopback port `8001` and GitHub owns external HTTPS forwarding.
- Stopping a Codespace stops demo compute but not the remote demo database.

## Safety

- `.env.demo` is never committed.
- Setup never runs migrations automatically against the shared database.
- Shared schema changes require the explicit `./scripts/shared-demo.sh migrate` command.
- The demo command surface does not expose `migrate:fresh` or unrestricted Artisan execution.
- Development/test commands never point at the shared demo database.
- One stable `APP_KEY` must be reused for the shared demo environment.

## Zero-cost baseline

For the unfunded stage, use a free managed PostgreSQL provider. Supabase Free is the recommended baseline because the current free plan provides a persistent Postgres project with a 500 MB database allowance; inactive free projects may pause and can be restored. This provider choice is operational, not domain authority, and may be replaced later without changing SongChart persistence boundaries.

## Activation

1. Provision a remote PostgreSQL database.
2. Save the connection string as `SONGCHART_DEMO_DATABASE_URL`.
3. Save one stable Laravel key as `SONGCHART_DEMO_APP_KEY`.
4. Run `./scripts/shared-demo.sh setup`.
5. Review the target database, then run `./scripts/shared-demo.sh migrate` explicitly.
6. Optionally run `./scripts/shared-demo.sh seed` for the governed provider registry seed.
7. Run `./scripts/shared-demo.sh ready` and open `./scripts/shared-demo.sh url`.

## Cost behavior

- Demo app/queue/Redis consume only the active local/Codespaces compute session.
- Remote database cost/lifecycle is owned by the selected provider.
- No repository daemon exists to keep a Codespace alive.

## Rollback

Remove `compose.demo.yml`, `docker/demo/env.example`, `scripts/shared-demo.sh`, and the shared-demo stack contract. Local development and canonical verification remain unchanged.

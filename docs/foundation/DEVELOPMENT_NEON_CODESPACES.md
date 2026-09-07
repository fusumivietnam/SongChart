# Durable Neon + Codespaces development

## Purpose

SongChart development data must survive Codespace rebuilds. GitHub Codespaces compute is disposable; the preferred durable PostgreSQL authority is Neon.

## Neon project

Create or select a Neon project/branch dedicated to SongChart development. Do not reuse production.

From Neon Connection Details, select the pooled connection string and retain TLS. The connection URL should identify the intended development database and include `sslmode=require` (or a stronger supported SSL mode).

## GitHub Codespaces secrets

Configure these repository Codespaces secrets in GitHub. Never commit their values:

- `SONGCHART_DEV_NEON_DATABASE_URL` — Neon pooled PostgreSQL connection string for the dedicated development database.
- `SONGCHART_DEV_ADMIN_PASSWORD` — strong development Super Admin bootstrap password. It is used only when the configured development administrator is missing.

The administrator email/name/role remain non-secret development configuration in `.env.docker`:

- `SONGCHART_DEV_ADMIN_EMAIL`
- `SONGCHART_DEV_ADMIN_NAME`
- `SONGCHART_DEV_ADMIN_ROLE`

## Bootstrap / reconnect

After adding or rotating Codespaces secrets, run:

```bash
./songchart dev setup
```

In Codespaces, setup will:

1. recover disposable Docker container objects without deleting named volumes;
2. copy `.env.docker.example` when `.env.docker` is absent;
3. when `SONGCHART_DEV_NEON_DATABASE_URL` is present, write it only to gitignored `.env.docker`;
4. force `SONGCHART_DEV_DATABASE_MODE=remote`;
5. derive `SONGCHART_DEV_DATABASE_EXPECTED_NAME` from the URL path;
6. require PostgreSQL TLS;
7. verify the connected database identity before migrations;
8. run migrations/provider registry bootstrap;
9. ensure the development administrator exists.

If the administrator already exists, its password and two-factor state are preserved. If it is missing and `SONGCHART_DEV_ADMIN_PASSWORD` is present, the account is recreated non-interactively as active and email-verified with the configured privileged role.

## Diagnostics

```bash
./songchart dev db status --json
./songchart ai doctor
```

Diagnostics must not print the Neon connection string, database password, administrator password, or raw PDO/driver exception messages.

## Recovery

SongChart still supports explicit portable development dumps:

```bash
./songchart dev db backup
./songchart dev db restore
```

Development database dumps live under `.songchart-db-backups/` and are separate from `.songchart-backups`, which is source/file overwrite recovery authority.

Neon branching / point-in-time recovery is an additional provider-owned recovery layer; it does not replace SongChart's explicit identity checks and portable dump path.

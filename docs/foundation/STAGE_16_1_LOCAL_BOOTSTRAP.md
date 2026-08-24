# Stage 16.1 — Local Development Bootstrap & Demo Readiness

> Historical note: this document records Stage 16.1 behavior. Current local administrator bootstrap is governed by Stage 16.7.12+, where `--admin` delegates to idempotent `admin:ensure-local`.

## Goal

Provide one safe Laravel command for preparing a local SongChartWeb checkout, one local-only readiness page, optional deterministic demo catalog data, and explicit login/admin URLs.

## Commands

```powershell
php artisan songchart:setup-local --demo
php artisan songchart:setup-local --admin
npm ci
npm run build
```

The command refuses to run outside `local` and `testing`. It never creates a default administrator or embeds a password. `--admin` launches the existing interactive `admin:create` command.

## Local URL

`/development/status` exists only in local/testing. It reports environment, APP_URL, app key, database connectivity, migrations, storage link, and Vite build state.

## Acceptance criteria

- Empty database can be migrated safely.
- Demo fixtures are opt-in and idempotent.
- No default privileged account is created.
- Important URLs are printed after setup.
- Local readiness page is unavailable in production routing.

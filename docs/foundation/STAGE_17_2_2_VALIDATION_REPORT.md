# Stage 17.2.2 — Validation Report

Status: source candidate; authoritative Docker canonical closure pending on target.

## Incident reproduced from target

Docker PostgreSQL was healthy. A fresh `php artisan tinker` process inside the `app` container resolved `DB_HOST=postgres` and `DB_DATABASE=songchart_docker`, while browser HTTP requests through Caddy still attempted `127.0.0.1:5432` against database `songchart`. Caddy configuration was confirmed to proxy to `app:8000`, eliminating host Laragon routing as the cause.

## Implemented

- replaced Docker app `php artisan serve` with direct PHP built-in server invocation;
- retained Laravel's framework development router script to preserve application route behavior;
- added architecture/static assertions that the direct-server command exists and `php artisan serve` does not return to Docker dev;
- left Caddy, PostgreSQL, Redis, queue topology, and MusicBrainz configuration unchanged.

## Validation performed in packaging environment

- changed PHP syntax checks: PASS;
- Docker local-development static contract: PASS;
- repository-state/documentation/official-source focused lanes: PASS where executable;
- target browser request and canonical Docker closure: pending target verification.

## Result

Stage 17.2.2 is ready for target verification. Recreate the `app` and `caddy` services, confirm browser requests use Docker PostgreSQL, confirm queue remains `Up`, then run canonical verification.

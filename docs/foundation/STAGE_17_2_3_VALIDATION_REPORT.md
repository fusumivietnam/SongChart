# Stage 17.2.3 — Validation Report

Status: source candidate; authoritative Docker canonical closure pending on target.

## Incident reproduced from target

After Stage 17.2.2, the Docker app container launched PHP's built-in server directly, but Laravel's framework router failed with `require_once(/workspace/index.php)` because the process working directory remained `/workspace`. SongChart's front controller is `/workspace/public/index.php`.

## Implemented

- preserved app service `working_dir: /workspace` for Composer, npm, Artisan, migrations, and one-off Compose commands;
- changed only the long-running HTTP command to `cd public` before invoking PHP's built-in server;
- retained Laravel's existing framework development router;
- kept Caddy, PostgreSQL, Redis, queue topology, MusicBrainz configuration, and persistent volumes unchanged;
- updated architecture/static verification to assert the corrected command.

## Validation performed in packaging environment

- PHP syntax sweep: pending/recorded by packaging checks;
- Docker local-development static contract: pending/recorded by packaging checks;
- repository-state/documentation/official-source focused lanes: pending/recorded by packaging checks;
- target browser request and canonical Docker closure: pending target verification.

## Result

Stage 17.2.3 is ready for target verification. Recreate `app` and `caddy`, confirm `/` and `/development/status` render through Docker, confirm `queue` remains `Up`, then run canonical verification.

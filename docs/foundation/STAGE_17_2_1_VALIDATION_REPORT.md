# Stage 17.2.1 — Validation Report

Status: source candidate; authoritative Docker canonical closure pending on target.

## Incident reproduced from target

The Docker queue container exited with `InvalidArgumentException: Redis connection [cache] not configured.` The Redis service itself was healthy. `config/cache.php` selected the named Redis connection `cache`, while `config/database.php` defined only `default`.

## Implemented

- added Laravel-native Redis `cache` connection in `config/database.php`;
- isolated cache to Redis logical database `1` while retaining queue/default Redis database `0`;
- made `REDIS_CACHE_CONNECTION=cache` and `REDIS_CACHE_DB=1` explicit for Docker app and queue services;
- added architecture coverage for the named Redis cache connection and Docker environment wiring.

## Validation performed in packaging environment

- changed PHP syntax checks: PASS;
- focused architecture/static verifier lanes: PASS where executable;
- Docker target runtime restart and queue persistence: pending target verification.

## Result

Stage 17.2.1 is ready for target verification. Queue container must remain `Up` and canonical verification must PASS before closure.

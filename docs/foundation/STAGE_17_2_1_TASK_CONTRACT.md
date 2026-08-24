# Stage 17.2.1 — Redis Cache Connection Runtime Corrective

Status: implementation candidate.

## Goal

Restore Docker queue-worker runtime by defining the named Redis `cache` connection already referenced by Laravel's Redis cache store, and make Docker development explicitly declare the cache connection/database used by both app and queue containers.

## Non-goals

- no queue topology redesign;
- no Redis Cluster/Sentinel introduction;
- no session-driver change;
- no database reset or volume removal;
- no provider pipeline behavior change beyond restoring worker startup.

## Acceptance criteria

- `config/database.php` defines Redis connections `default` and `cache`;
- Redis cache store continues to default to connection `cache`;
- Docker app and queue explicitly use `REDIS_CACHE_CONNECTION=cache` and Redis DB `1` for cache isolation;
- queue worker remains on the existing SongChart queue list;
- architecture coverage prevents removal of the required named connection;
- existing `.env` and Docker data volumes remain untouched.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `config/cache.php`
- `config/database.php`
- `config/queue.php`
- `compose.dev.yml`

### Installed versions

No dependency changes. Runtime remains Laravel 13 / PHP 8.5, Redis 7.4 and the existing Docker development profile.

### Official external sources

- Laravel 13 Redis: https://laravel.com/docs/13.x/redis
- Laravel 13 Cache: https://laravel.com/docs/13.x/cache

Laravel supports multiple named Redis connections in `config/database.php`; the cache store selects a named Redis connection. Stage 17.2 already configured the Redis cache store to use `cache`, so that named connection must exist at runtime.

### Native capability assessment

Laravel's native Redis manager and Redis cache store already provide the required connection selection and cache isolation. No custom Redis client or package is needed.

### Custom implementation justification

The corrective is repository configuration only: define the missing native Laravel Redis connection and assert the Docker environment contract. No custom runtime abstraction is introduced.

## Security, authorization, and data impact

No authorization changes. Cache uses Redis logical database `1`, separate from the default Redis logical database `0` used by queues. No persistent PostgreSQL schema changes are introduced.

## Tests and verification

- PHP syntax sweep;
- `tests/Architecture/DockerRedisCacheConnectionConfigurationTest.php`;
- `tests/Architecture/DockerLocalDevelopmentTest.php`;
- Docker local-development verifier;
- repository-state/documentation/official-source verifiers;
- final target closure through `verify-songchart.bat`.

## Rollback

Restore Stage 17.2 `config/database.php`, `compose.dev.yml`, and architecture tests. No database migration rollback is required.

# Stage 16.3 — Laravel Redis Queue & Horizon Deployment Readiness

## Authority and official sources

### Repository authorities

`config/queue.php`, `config/songchart.php`, Stage 16.2 Discovery projection contracts, provider ingestion jobs, `docs/project/stack/STACK_OVERVIEW.md`, `docs/project/stack/impact-test-map.json`, repository quality/release Composer scripts, and the PostgreSQL release lane remain authoritative.

### Installed versions

Repository baseline becomes PHP 8.5+ / Laravel 13 / Pest 4 / Larastan 3. PostgreSQL 17+ remains release/integration DB authority. Native Windows/Laragon remains a supported development target.

### Official external sources

- Laravel 13 Horizon documentation: Redis requirement, code-driven supervisors, dashboard authorization, retries/timeouts/backoff, tags, metrics snapshots and process-monitor deployment.
- Official `laravel/horizon` 5.x Composer manifest: Laravel 13 support plus mandatory `ext-pcntl` and `ext-posix` runtime requirements.
- Official Laravel 13 framework Composer manifest: PHP `^8.3` framework compatibility; SongChart deliberately raises its own application baseline to PHP `^8.5`.

### Native capability assessment

Laravel queues already provide Redis connections, queue routing, retries, backoff, timeouts, uniqueness and worker commands. Horizon adds first-party Linux/WSL supervision/metrics but cannot be a mandatory dependency while native Windows PHP is supported because its Composer requirements include POSIX-only extensions.

### Custom implementation justification

SongChart only adds a small queue taxonomy, workload-specific operational defaults, authorization mapping and static governance. It does not implement a custom worker, queue dashboard, scheduler or metrics engine. Horizon configuration and its standard authorization Gate are kept ready; the official package auto-discovers its own provider when installed on a compatible OS.

## Objective

Standardize asynchronous workloads on Laravel Redis queues, harden job operational metadata, prepare first-party Horizon operations for Linux/WSL, and raise the repository PHP baseline to 8.5 without breaking native Laragon development.

## Scope

- Redis becomes queue default; retry-after exceeds longest job timeout
- canonical queue taxonomy for Discovery/provider/default workloads
- bounded retry/backoff/timeout/tags for Discovery projection jobs
- provider ingestion routing to import/normalization queues
- Horizon config with workload-aware supervisors and wait thresholds
- Horizon-ready config, package auto-discovery and operations-only `viewHorizon` authorization
- five-minute Horizon snapshots when package exists
- PHP 8.5 Composer/CI/Laragon baseline
- queue infrastructure docs, tests, verifier and impact-map authority

## Non-goals

- forcing Horizon to run on native Windows
- `--ignore-platform-reqs`
- custom queue dashboard in business admin
- replacing Laravel queue primitives
- Pulse/Nightwatch observability (later stages)
- RBAC package migration

## Expected files

- `app/Enums/QueueName.php`
- `config/horizon.php`
- `config/queue.php`
- `config/songchart.php`
- `bootstrap/providers.php`
- `routes/console.php`
- Discovery/provider queued jobs
- `.env.example`
- `composer.json`
- `.github/workflows/tests.yml`
- `scripts/setup-laragon.bat`
- `scripts/verify-queue-infrastructure.php`
- `docs/operations/queue-infrastructure.md`
- `tests/Unit/QueueInfrastructureContractTest.php`
- `tests/Architecture/QueueInfrastructureBoundaryTest.php`

## Allowed incidental files

- `README.md`
- `app/Enums/UserRole.php`
- `app/Models/User.php`
- `app/Providers/AppServiceProvider.php`
- `docs/project/stack/STACK_OVERVIEW.md`
- `docs/project/stack/impact-test-map.json`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_3_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_3_LEGACY_CATALOG_ADMINISTRATION_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_3_LEGACY_CATALOG_ADMINISTRATION_VALIDATION_REPORT.md`

## Scope deviations

Horizon is intentionally not added to mandatory Composer `require`/`require-dev`: official Horizon 5.x requires `ext-pcntl` and `ext-posix`, which native Windows PHP cannot provide. The repository ships a conditional, ready-to-activate official Horizon profile instead.

## Tests and verification

Run queue infrastructure, Laravel alignment, Discovery 16.0-16.2, documentation, repository-state, official-source, authority-dependency, impact-map, type/code-generation, performance, database/CI and PHP syntax checks. On the Laragon target run Pint, Larastan/PHPStan and focused Pest. On Linux/WSL production/staging, additionally install `laravel/horizon:^5.47`, verify `/horizon` authorization, `horizon:status`, metrics snapshots and process-monitor deployment.

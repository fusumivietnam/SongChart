# Stage 16.2 — Discovery Projection Pipeline

## Authority and official sources

### Repository authorities

`docs/discovery/domain-contract.md`, `docs/discovery/rule-engine.md`, `docs/discovery/projection-contract.md`, `docs/discovery/query-performance-contract.md`, `docs/project/domain/domain-contracts.json`, `docs/project/domain/schema-ownership.json`, the repository quality/release Composer scripts, and Stage 16.0/16.1 Discovery contracts remain authoritative. This stage materializes read models only; canonical catalog entities remain the source of truth.

### Installed versions

Laravel 13 / PHP 8.3+ / Pest 4 / Larastan 3 repository baseline. PostgreSQL 17+ remains the production and integration database authority.

### Official external sources

- Laravel 13 queue documentation for queued jobs, uniqueness, queue selection and worker execution.
- Laravel 13 task scheduling documentation for scheduled commands, overlap prevention and single-server execution.
- Laravel 13 database documentation for transactions, pessimistic locking and query-builder pagination patterns.

### Native capability assessment

Laravel already provides queues, unique jobs, command scheduling, database transactions, row locks and Eloquent/query-builder primitives required for a bounded projection pipeline. No additional queue, scheduler, CQRS or search framework is needed for Stage 16.2.

### Custom implementation justification

SongChart Discovery requires provider-neutral canonical mapping, typed Stage 16.1 rule evaluation, bounded top-K materialization, editorial pin/exclusion merge semantics and immutable projection revisions. These are SongChart domain rules and therefore belong in a small application/domain pipeline over Laravel-native infrastructure rather than in a generic third-party recommendation framework.

## Objective

Materialize bounded, deterministic Discovery projections from canonical SongChart entities using the Stage 16.1 typed rule engine, without public-request aggregation or provider coupling.

## Scope

- bounded canonical entity source batches and exact-ID lookup
- manual, derived and hybrid materialization
- editorial exclusions and pinned positions
- immutable projection revisions with transactional revision allocation
- queued rebuild job, CLI rebuild/backfill entry point and fifteen-minute scheduling
- projection reader/writer/pipeline contracts and Laravel bindings
- focused unit/architecture/static verification

## Non-goals

- public Discovery UI/API composition
- chart velocity/momentum metrics (Stage 17)
- provider API reads
- recommendation ML/personalization
- arbitrary SQL rule push-down

## Expected files

- `app/Domain/Discovery/Contracts/DiscoveryEntitySource.php`
- `app/Domain/Discovery/Contracts/DiscoveryEditorialStateReader.php`
- `app/Domain/Discovery/Contracts/DiscoveryProjectionWriter.php`
- `app/Domain/Discovery/Contracts/DiscoveryProjectionPipeline.php`
- `app/Application/Discovery/Projections/DefaultDiscoveryProjectionPipeline.php`
- `app/Support/Discovery/EloquentDiscoveryEntitySource.php`
- `app/Support/Discovery/DatabaseDiscoveryChannelRepository.php`
- `app/Support/Discovery/DatabaseDiscoveryEditorialStateReader.php`
- `app/Support/Discovery/DatabaseDiscoveryProjectionStore.php`
- `app/Jobs/Discovery/BuildDiscoveryProjection.php`
- `app/Console/Commands/RebuildDiscoveryProjectionsCommand.php`
- `tests/Unit/DiscoveryProjectionPipelineTest.php`
- `tests/Architecture/DiscoveryProjectionBoundaryTest.php`
- `scripts/verify-discovery-projection-pipeline.php`
- `docs/discovery/projection-pipeline.md`
- `docs/project/domain/domain-contracts.json`
- `composer.json`
- `config/songchart.php`
- `routes/console.php`

## Allowed incidental files

- `.env.example`
- `README.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_2_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_2_LEGACY_ADMIN_INFORMATION_ARCHITECTURE_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_2_LEGACY_ADMIN_INFORMATION_ARCHITECTURE_VALIDATION_REPORT.md`

## Scope deviations

None.

## Tests and verification

Run Discovery 16.0/16.1/16.2 verifiers, documentation, repository-state, official-source, authority-dependency, impact-map, domain-contract, schema-ownership, type-guardrail, code-generation, performance, database-authority and PHP syntax checks. On the Laragon target with dependencies installed, run Pint, focused Larastan/PHPStan, focused PostgreSQL Pest tests, then the normal project quality/release gates before delivery acceptance.

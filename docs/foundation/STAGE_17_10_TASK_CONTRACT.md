# Stage 17.10 Task Contract — Enrichment Orchestrator

## Goal
Turn read-only enrichment plans into governed executable work without allowing orchestration jobs to bypass provider gates or canonical mutation boundaries.

## Delivered candidate slices

### v1 — deterministic scheduling
- stable idempotency key per entity/need/provider;
- provider-disabled work is deferred;
- duplicate equivalent needs collapse;
- priority and cost determine ordering.

### v2 — persistence and database idempotency
- persisted `enrichment_attempts` ledger;
- database-unique idempotency key;
- application/store persistence boundary.

### v3 — queue dispatch and provider rate gate
- queued `GateEnrichmentAttempt`;
- existing provider-neutral rate policy/request gate is reused;
- retryable gate failures defer and retry; terminal gate failures fail closed.

### v4 — provider execution outcome boundary
- `ExecuteEnrichmentAttempt` begins only from `ready`;
- provider execution goes through `EnrichmentExecutor`;
- safe default is `review_required` when no governed executor exists;
- retryable execution returns through `GateEnrichmentAttempt`;
- success evidence is persisted without canonical entity mutation;
- review/failure outcomes are explicit and durable.

### v5 — governed MusicBrainz execution and admission
- `GovernedEnrichmentExecutor` reuses the existing MusicBrainz catalog adapter and normalization pipeline;
- fresh identity evidence is admitted without a redundant provider request;
- configurable UTC daily execution budget provides coarse provider-cost admission without duplicating the rate gate;
- zero, ambiguous and unsupported outcomes fail safe to review;
- normalized evidence is persisted, never directly promoted into canonical catalog records.

## Non-goals for v5
No direct canonical writes, no automatic evidence promotion, no new third-party package, no second MusicBrainz HTTP client, and no YouTube media expansion.

## Cleanup
`docker-dev-ready.bat` and `docker-dev-cycle.bat` remain removed. Additional compatibility wrappers are removed only after repository contract/reference consumers are migrated.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `PROJECT_AUTHORITY.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/stack/FRAMEWORK_BASELINE.md`
- `docs/project/stack/PACKAGE_ADOPTION_POLICY.md`
- Existing provider catalog, rate-policy, request-gate, normalization, identity-bridge, and canonical-mutation boundaries remain authoritative for Stage 17.10.

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.5` | `composer.json` / canonical Docker image |
| Laravel framework | `^13.0` | `composer.json` and resolved `composer.lock` |
| PostgreSQL | major 18 | repository Docker/stack authority |
| Redis | repository Docker development/verification baseline | Compose authority |
| Pest | `^4.0` | `composer.json` and `composer.lock` |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | Laravel 13 queue documentation | queued jobs, retry/backoff, job dispatch semantics | 2026-08-24 |
| Laravel | Laravel 13 rate-limiting documentation | reuse of framework/provider request admission primitives | 2026-08-24 |
| Laravel | Laravel 13 database/query-builder documentation | atomic insert/idempotency persistence primitives | 2026-08-24 |
| PostgreSQL | PostgreSQL 18 constraints documentation | database-enforced unique idempotency boundary | 2026-08-24 |
| MusicBrainz | MusicBrainz Web Service documentation | governed external metadata retrieval through the existing adapter | 2026-08-24 |

No new third-party package or alternate provider client is introduced by Stage 17.10 v1-v5.

### Native capability assessment

- Capability owner: Laravel queue/database primitives plus the repository's existing provider infrastructure.
- Native/first-party capability available: partial.
- Selected official primitives: Laravel queued jobs, retry/release semantics, database insert/unique constraints, existing provider rate/request gate, and the existing MusicBrainz adapter/normalizer.
- Why it satisfies the requirement: scheduling, durable idempotency, queue execution, retry flow, and provider admission can be built from existing framework/database primitives without introducing a second queue system, provider HTTP client, or canonical-write path.
- PostgreSQL remains the persistence authority for orchestration attempts and idempotency; Redis remains queue/runtime infrastructure rather than a second data authority.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: Laravel and PostgreSQL provide execution primitives but do not define SongChart's enrichment-plan semantics, provider-neutral work ordering, evidence governance, review outcomes, freshness admission, or canonical mutation boundary.
- Narrow custom boundary: deterministic schedule builder, enrichment attempt ledger/store, orchestration jobs, governed executor routing, evidence result DTOs/outcomes, and admission policy integration.
- Framework primitives reused: Laravel container bindings, queued jobs, retry/release behavior, Eloquent/query builder, configuration, and existing SongChart provider gates/adapters.
- Existing code reused: provider catalog adapter registry, MusicBrainz adapter, normalization pipeline, provider rate-policy registry, provider request gate, identity bridge, and canonical mutation governance.
- Non-goals: no custom queue broker, no duplicate MusicBrainz client, no alternate canonical datastore, no automatic canonical mutation, no microservice split, and no new package where an existing/native capability already satisfies the requirement.

## Tests and verification

- Fast development validation: `songchart.bat dev ready` plus focused Stage 17.10 Unit/Feature tests.
- Deterministic scheduling: `tests/Unit/.../BuildEnrichmentScheduleTest.php` (repository path as implemented).
- Persistence/queue/execution behavior: `tests/Feature/Catalog/EnrichmentQueueDispatchTest.php` and Stage 17.10 persistence coverage.
- Provider execution success payloads are compared semantically rather than relying on PostgreSQL JSON object key ordering.
- Docker local-development architecture contract remains protected by `tests/Architecture/DockerLocalDevelopmentTest.php`.
- Official-source governance: `composer official-sources:verify` / `php scripts/verify-official-sources.php`.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify` via `songchart.bat verify`.
- Canonical verification is intentionally not nested inside the fast `dev cycle`; it is run only for candidate/stage closure.


### V6 evidence admission contract

Stage 17.10 v6 inserts a provider-neutral evidence admission boundary after provider execution and normalization. The existing normalized-provider validator remains the structural validation authority. The admission policy may classify evidence as `admissible`, `review_required`, or `rejected`, but none of those decisions authorize canonical mutation. `succeeded` in the enrichment-attempt ledger therefore means evidence passed admission, not that canonical data was changed. Rejected evidence is persisted as terminal `rejected` with its admission metadata for diagnosis and auditability.

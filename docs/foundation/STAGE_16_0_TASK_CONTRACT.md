# Stage 16.0 — Discovery Domain Contracts

## Authority and official sources

### Repository authorities

`AGENTS.md`, `docs/project/domain/domain-contracts.json`, `docs/project/domain/schema-ownership.json`, `docs/project/docs/ARCHITECTURE.md`, `docs/project/docs/ENGINEERING_RULES.md`, the repository impact-test map, PostgreSQL database authority and existing provider/canonical mutation boundaries remain authoritative. Discovery is added as a read-oriented domain and may not weaken those contracts.

### Installed versions

Laravel 13 / PHP 8.3+ / Pest 4 / Larastan 3 repository baseline. PostgreSQL 17+ remains release and integration database authority.

### Official external sources

No new external package, API, provider schema or framework capability is introduced by this stage. Existing installed Laravel migration/schema APIs and PHP enum/readonly language capabilities are sufficient; implementation is governed by the pinned repository baseline rather than a new external dependency.

### Native capability assessment

Laravel migrations, container contracts, backed enums, validation boundaries and Pest architecture/unit tests cover the required foundation. A third-party rules engine, CMS framework, recommendation package or CQRS framework is unnecessary for Stage 16.0.

### Custom implementation justification

SongChart needs a product-specific boundary between canonical music identity and discovery/editorial composition. A small typed contract layer is justified to prevent provider coupling, raw SQL rules, request-time aggregation and divergent future implementations across homepage, country pages, smart collections and admin views.

## Changed authorities

No authority registered in the authority-dependency registry changes semantic state in this stage. Discovery extends existing domain/schema governance without changing the PostgreSQL test authority or attention-first admin dashboard authority.

## Scope

- Restore the static-analysis-safe AdminDashboardTest reflection hotfix carried by candidate v9.
- Add the machine-readable Discovery contract to `domain-contracts.json`.
- Define typed channel mode/status/entity/layout/surface/rule/operator/capability/event contracts.
- Define `DiscoveryChannel`, publication window, rule/sort/field/projection DTOs and repository/registry/read contracts.
- Define command/query DTO boundaries without handlers or UI.
- Add PostgreSQL storage contracts for channels, manual items, exclusions, placements and projections.
- Classify all new tables in schema ownership.
- Add executable discovery contract verification, unit tests and architecture boundary tests.
- Document domain, rule, projection, invariant and query-performance contracts.

## Non-goals

- No homepage or public Discovery UI changes.
- No admin Discovery composer.
- No rule evaluator/query compiler.
- No derived/hybrid projection builder.
- No cache implementation.
- No chart velocity, momentum or recommendation algorithm.
- No provider API call, provider import behavior or canonical mutation.
- No new package dependency.

## Expected files

- `tests/Feature/AdminDashboardTest.php`
- `app/Domain/Discovery/**`
- `app/Application/Discovery/**`
- `database/migrations/2026_08_10_000200_create_discovery_domain_tables.php`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/project/stack/impact-test-map.json`
- `docs/discovery/**`
- `scripts/verify-discovery-domain-contracts.php`
- `tests/Unit/DiscoveryDomainContractsTest.php`
- `tests/Architecture/DiscoveryDomainBoundaryTest.php`
- `composer.json`
- `README.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_0_VALIDATION_REPORT.md`

## Allowed incidental files

- `docs/DOCUMENTATION_INDEX.md`
- `.github/workflows/tests.yml`

## Scope deviations

Stage numbering follows the requested Discovery roadmap identifier even though this candidate is built on the later Stage 16.8.2 repository baseline. Candidate version `v10` is the delivery lineage authority. The Closure v8 archive omitted `.github/workflows/tests.yml` although existing CI/database authority verifiers require it; v10 restores that already-governed PostgreSQL-only workflow without changing its authority semantics.

## Tests and verification

Run PHP syntax over changed PHP, JSON decoding, Discovery contract verifier, domain-contract verifier, schema-ownership verifier, documentation verifier, repository-state verifier, impact-map verifier, source-package verifier and architecture/unit tests when vendor dependencies are available. On the Laragon target also run Pint, full Larastan/PHPStan, PostgreSQL tests, frontend build and `composer release:verify`.

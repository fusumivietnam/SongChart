# Stage 16.1 — Unified Entity Rule Engine

Status: task contract.

## Goal

Turn the Stage 16.0 Discovery rule contracts into an executable, provider-neutral rule engine over typed canonical entity snapshots without adding public request-time rule execution or SQL compilation.

## Non-goals

- No SQL/query-builder compiler or database push-down.
- No provider API, provider payload field, provider identifier or canonical mutation.
- No projection jobs/materialization (Stage 16.2).
- No editorial/admin UI (Stage 16.3).
- No public Discovery API/UI.
- No personalization or recommendation ML.
- No chart velocity, momentum or other Stage 17 intelligence metric field.
- No new package dependency.

## Acceptance criteria

- A typed `DiscoveryRuleEngine` validates and compiles Stage 16.0 rules into in-process predicates.
- A canonical `DiscoveryFieldRegistry` exposes only accepted Artist/Recording/Release/Collection fields and fails closed for unknown fields.
- Rule values are validated against declared field data types and allowed operators.
- `in/not_in` are capped at 100 typed values; `exists/not_exists` require null rule values.
- Sorting validates fields, rejects duplicates/mixed entity types and appends `id ASC` as an implicit deterministic tie-breaker when absent.
- Canonical Eloquent entities are mapped to `DiscoveryEntitySnapshot` before rule evaluation.
- Rule-engine/application code contains no raw SQL or provider coupling.
- Container bindings resolve the field registry, mapper and rule engine through typed contracts.

## Affected modules and boundaries

- `app/Domain/Discovery/**`: executable contracts and typed snapshots only.
- `app/Application/Discovery/Rules/**`: rule compilation/evaluation and deterministic sorting.
- `app/Support/Discovery/**`: canonical field registry and Eloquent-to-snapshot adapter.
- `app/Providers/AppServiceProvider.php`: dependency bindings.
- Discovery machine authority, documentation, tests and static verifier.
- No route, controller, public UI, provider or schema change.

## Expected files

- `app/Domain/Discovery/Contracts/DiscoveryRuleEngine.php`
- `app/Domain/Discovery/Contracts/DiscoveryEntityMapper.php`
- `app/Domain/Discovery/DTO/DiscoveryEntitySnapshot.php`
- `app/Domain/Discovery/DTO/CompiledDiscoveryRule.php`
- `app/Application/Discovery/Rules/DefaultDiscoveryRuleEngine.php`
- `app/Support/Discovery/CanonicalDiscoveryFieldRegistry.php`
- `app/Support/Discovery/CanonicalDiscoveryEntityMapper.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Unit/DiscoveryRuleEngineTest.php`
- `tests/Architecture/DiscoveryRuleEngineBoundaryTest.php`
- `scripts/verify-discovery-rule-engine.php`
- `composer.json`
- `docs/project/domain/domain-contracts.json`
- `docs/project/stack/impact-test-map.json`
- `docs/discovery/rule-engine.md`
- `docs/discovery/rule-schema.md`
- `docs/discovery/README.md`
- `README.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_1_VALIDATION_REPORT.md`

## Allowed incidental files

- `docs/foundation/STAGE_16_1_LEGACY_LOCAL_BOOTSTRAP_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_1_LEGACY_LOCAL_BOOTSTRAP_VALIDATION_REPORT.md`
- delivery/checksum/changeset metadata.

## Scope deviations

The existing repository history already used numeric Stage 16.1 for Local Development Bootstrap before the Discovery roadmap reset at Stage 16.0. The prior 16.1 governance records are preserved byte-for-byte under `STAGE_16_1_LEGACY_LOCAL_BOOTSTRAP_*`; the current-stage authority uses Stage 16.1 for Unified Entity Rule Engine as requested by the Discovery roadmap.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/project/docs/ARCHITECTURE.md`
- `docs/project/docs/ENGINEERING_RULES.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/discovery/domain-contract.md`
- `docs/discovery/rule-schema.md`
- `docs/discovery/invariants.md`
- `docs/project/stack/impact-test-map.json`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | 8.3+ repository baseline | `composer.json` |
| Laravel | 13.x repository baseline | `composer.json` / release lock authority |
| Pest | 4.x repository baseline | `composer.json` / release lock authority |
| Larastan | 3.x repository baseline | `composer.json` / release lock authority |
| PostgreSQL | 17+ release authority | repository database authority docs |

### Official external sources

No new external API, provider schema, package or framework integration is introduced. Existing PHP closures/enums/readonly DTOs and Laravel container/Eloquent adapter capabilities already pinned by the repository are sufficient.

### Native capability assessment

- Capability owner: SongChart Discovery domain/application layer.
- Native/first-party capability available: partial.
- Selected official API or primitive: PHP typed objects/closures plus Laravel service-container bindings and Eloquent model reads.
- Why it satisfies the requirement: the rule grammar is SongChart-specific, while execution and dependency wiring require no third-party rules package.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: Laravel does not define SongChart's provider-neutral Discovery rule grammar, canonical field registry or deterministic cross-entity evaluator.
- Narrow custom boundary: typed validation/evaluation over `DiscoveryEntitySnapshot` only.
- Framework primitives reused: Laravel container and canonical Eloquent read adapter.
- Non-goals: SQL DSL, CMS/recommendation framework, provider ingestion, canonical writes.

## Domain contract and use-case data surface

- Actor and preconditions: internal application/projection worker code; Stage 16.1 does not expose a public actor route.
- Input types and identifier formats: `DiscoverableEntityType`, `DiscoveryRuleSet`, `DiscoveryRuleCondition`, `DiscoverySort`, ULID/string canonical entity ID snapshots.
- Exact entity fields read: canonical Artist/Recording/Release/Collection fields enumerated by `CanonicalDiscoveryFieldRegistry`.
- Exact entity fields written: none.
- Null/unknown semantics: unknown fields/operators/types fail closed; missing/null entity values satisfy `not_exists` and fail ordinary comparisons.
- Output DTO/presentation contract: `CompiledDiscoveryRule` and sorted `DiscoveryEntitySnapshot` lists; no presentation payload.
- Route/API contract: none.
- Relationship invariants: one compiled rule and one sort execution operate on one declared entity type only.
- Contract changes required: yes.
- `domain-contracts.json` entries affected: `discovery.rule_engine`.

## Security, authorization, and data impact

No authentication/authorization policy or sensitive-data retention behavior changes. The stage reduces injection/coupling risk by prohibiting arbitrary fields and SQL compilation. No provider data is accessed and no database schema/data migration is introduced.

## Tests and verification

- `tests/Unit/DiscoveryRuleEngineTest.php` covers AND matching, typed validation, exists/ordered comparison, deterministic sorting and entity-type safety.
- `tests/Architecture/DiscoveryRuleEngineBoundaryTest.php` checks typed contracts and rejects provider/raw-SQL coupling.
- `scripts/verify-discovery-rule-engine.php` statically enforces Stage 16.1 authority.
- Existing Discovery/domain/impact-map/documentation/repository-state/official-source/schema/runtime/database/CI verifiers remain required.
- Run Pint and Larastan/PHPStan in the target repository with Composer dependencies installed.
- PostgreSQL release tests remain authoritative even though Stage 16.1 adds no migration.
- Archive build environments without `vendor/` may only claim syntax/static governance and pure-PHP smoke verification.

## Documentation impact

Update Discovery rule-engine/rule-schema docs, machine-readable Discovery authority, impact-test map, README current-stage pointer, validation report and development history. Preserve the earlier legacy numeric Stage 16.1 records explicitly.

## Rollback

Remove Stage 16.1 rule-engine/mapper/registry files and bindings, restore the Stage 16.0 Discovery machine authority/docs/current-stage pointer, and restore the original numeric Stage 16.1 governance records from the preserved legacy copies. No database rollback is required.

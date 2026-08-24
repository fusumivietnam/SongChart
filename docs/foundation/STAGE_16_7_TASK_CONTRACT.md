# Stage 16.7 — Application Data Boundary

Status: implementation candidate.

## Goal

Define an executable application data boundary that keeps HTTP controllers as transport adapters, classifies existing admin query surfaces as read models, preserves explicit write-service ownership, and establishes query-budget registration before public discovery surfaces expand.

## Non-goals

- full CQRS;
- repository wrappers for trivial reads;
- changing database schema;
- changing provider normalization or canonical identity semantics;
- changing authorization roles/capabilities;
- introducing a new persistence package;
- guessing hard query limits without representative PostgreSQL fixtures;
- moving every existing admin read class into a new namespace in one stage.

## Acceptance criteria

- `application-data-boundary.json` is the machine authority for read/write persistence boundaries;
- controllers do not use direct Eloquent query composition or Query Builder;
- controllers do not open transactions or persist models;
- Extension admin index/show reads are moved to an Application Query read model;
- existing `Support/Admin` query classes are explicitly registered as read models rather than left as ambiguous support code;
- registered read models contain no persistence mutation signals;
- `PrivilegedUserAdministration` remains an explicit write service and may own transactions/locking;
- read models may use Eloquent/Query Builder without ceremonial repository wrappers;
- external provider access remains behind provider contracts/adapters;
- query-budget surfaces are registered, but hard limits are not invented without representative PostgreSQL evidence;
- architecture verifier and Architecture test consume the machine authority;
- consumer graph/impact graph route Application Data Boundary changes to the correct verification owners;
- no new standalone verifier is added.

## Affected modules and boundaries

HTTP controllers, admin read-model/query surfaces, application query layer, architecture governance, query-budget registration. No persistent schema or product semantics change.

## Changed authorities

- `application-data-boundary`
- `query-budget`
- `executable-repository-authority`
- `verification-consumer-graph`
- `ai-development-protocol`

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/domain/application-data-boundary.json`
- `docs/project/performance/query-budget-contract.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

No dependency change. PHP 8.5 / Laravel 13 / Pest 4 / Larastan 3 / PostgreSQL 18 / Node 22 remain authoritative.

### Official external sources

No new external package or API is introduced. Laravel controllers, service container, Eloquent and Query Builder already provide the required native primitives.

### Native capability assessment

Laravel's controller DI, Eloquent, Query Builder, Actions/services and service container are sufficient. A CQRS/event-sourcing package or repository framework is unnecessary.

### Custom implementation justification

SongChart needs a project-specific executable classification of transport/read/write surfaces so AI/code generation cannot gradually reintroduce persistence logic into controllers or mutate through read models.

## Expected files

- `docs/project/domain/application-data-boundary.json`
- `docs/project/performance/query-budget-contract.json`
- `app/Application/Admin/Queries/ExtensionReadModel.php`
- `app/Http/Controllers/Admin/ExtensionController.php`
- `app/Support/Admin/AdminDashboardSnapshot.php`
- `app/Support/Admin/AdminInformationArchitecture.php`
- `app/Support/Admin/CatalogAdministration.php`
- `app/Support/Admin/IdentityConflictReviewConsole.php`
- `app/Support/Admin/PrivilegedAuditConsole.php`
- `app/Support/Admin/ProviderOperationsConsole.php`
- `app/Support/Admin/PrivilegedUserAdministration.php`
- `scripts/verify-architecture-conformance.php`
- `tests/Architecture/ArchitectureConformanceTest.php`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/engineering/regression-ledger.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/generated/repository-contract-manifest.json`
- `candidate-verification.json`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/foundation/STAGE_16_6_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_7_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_VALIDATION_REPORT.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/DOCUMENTATION_INDEX.md`

- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/domain/model-schema-contracts.json`
- `app/Support/Engineering/RepositoryContractResolver.php`
- `app/Console/Commands/SongChartDoctorCommand.php`
- `scripts/compile-repository-contracts.php`
- `scripts/verify-repository-contract-compiler.php`
- `scripts/resolve-repository-impact.php`
- `scripts/verify-artifact-provenance.php`
- `scripts/package-verified-source.php`
- `scripts/snapshot-postgres-schema.php`
- `scripts/verify-package-upstream-adaptations.php`
- `scripts/record-canonical-verification.php`
- `tests/Architecture/ExecutableRepositoryAuthorityTest.php`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `scripts/verify-ai-development-protocol.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`
- `scripts/verify-identity-conflict-review-ui.php`
- `docs/foundation/history/STAGE_16_7_IDENTITY_CONFLICT_REVIEW_UI_TASK_CONTRACT.md`
- `docs/foundation/history/STAGE_16_7_IDENTITY_CONFLICT_REVIEW_UI_VALIDATION_REPORT.md`
## Allowed incidental files

- exact-tree generated repository contract manifest;
- formatter-only changes to listed PHP files;
- packaging metadata outside repository.

## Scope deviations

None planned.

## Security, authorization, and data impact

No authorization grant changes. No schema change. The stage reduces accidental persistence bypass risk by keeping HTTP controllers outside direct data access and keeping read models mutation-free.

## Tests and verification

Focused:
- `composer architecture:verify`;
- Architecture `ArchitectureConformanceTest`;
- repository compiler / consumer graph;
- authority dependency / impact map;
- code-generation guardrails;
- existing admin Feature tests.

Candidate:
- `composer stage:verify`.

Canonical:
- `songchart verify`.

Packaging:
- authoritative full source only from exact canonical target via `composer release:package`.

## Rollback

File-only rollback; no database/dependency rollback.

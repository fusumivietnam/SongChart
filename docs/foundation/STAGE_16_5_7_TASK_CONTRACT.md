# Stage 16.5.7 — Single Verification Authority & Consumer Graph Closure

Status: implementation candidate.

## Goal

Make verification ownership executable before expensive closure runs. Every verifier and Architecture test must route to exactly one semantic authority through the existing repository compiler/resolver so authority changes cannot leave stale consumers to be discovered one-by-one during canonical verification.

## Non-goals

- reducing behavioral coverage merely to lower file counts;
- deleting all specialized verifiers;
- moving runtime behavior tests into static JSON;
- introducing another standalone verifier for the consumer graph;
- changing application/domain behavior;
- changing database schema or dependencies;
- verification evidence caching.

## Acceptance criteria

- `verification-consumer-graph.json` is the machine routing authority for verifier and Architecture consumers.
- every `scripts/verify-*.php` file matches exactly one ownership rule;
- every `tests/Architecture/*.php` file matches exactly one ownership rule;
- each rule declares `declarative|behavioral` classification and one or more registered semantic authorities;
- every verifier has at least one Composer execution owner;
- unknown semantic authorities fail repository compiler verification;
- overlapping ownership rules fail repository compiler verification;
- unowned new verifier/Architecture files fail repository compiler verification;
- registered cross-layer literal boundaries fail repository compiler verification before PostgreSQL/Pest closure;
- `RepositoryContractResolver::compileManifest()` includes the resolved verification-consumer graph;
- impact resolution maps changed verifier/Architecture paths back to semantic authorities;
- no new graph-specific verifier script is introduced; `repository-compiler:verify` owns execution;
- active AI/project docs require exhaustive consumer reconciliation after authority changes;
- Stage 16.5.6 is recorded closed from v24.3 canonical PASS.

## Affected modules and boundaries

Engineering verification governance only. No product routes, authorization, provider semantics, persistent schema, dependencies, or user-facing runtime behavior change.

## Changed authorities

- `verification-consumer-graph`
- `executable-repository-authority`
- `verification-topology`
- `ai-development-protocol`

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/impact-test-map.json`

### Installed versions

No dependency change. Existing PHP 8.5 / Laravel 13 / Pest 4 / Larastan 3 / PostgreSQL 18 / Node 22 authorities remain unchanged.

### Official external sources

No external dependency, API, or framework capability is added. This stage consolidates repository-owned verification routing using existing PHP/Composer/Laravel tooling.

### Native capability assessment

The existing `RepositoryContractResolver`, compiler manifest, Composer scripts, `fnmatch`, and Architecture suite are sufficient. A new package or independent verifier framework is unnecessary.

### Custom implementation justification

SongChart needs project-specific mapping from semantic authorities to repository verifier/Architecture consumers. Existing Composer/Pest tools execute commands/tests but do not know SongChart authority ownership or cross-layer literal boundaries.

## Expected files

- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `app/Support/Engineering/RepositoryContractResolver.php`
- `scripts/verify-repository-contract-compiler.php`
- `scripts/resolve-repository-impact.php`
- `tests/Architecture/ExecutableRepositoryAuthorityTest.php`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/ai-development-contract.json`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/engineering/regression-ledger.json`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/generated/repository-contract-manifest.json`
- `candidate-verification.json`
- `docs/foundation/STAGE_16_5_6_VALIDATION_REPORT.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_16_5_7_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_5_7_VALIDATION_REPORT.md`

Registered dependents reviewed for authority closure:
- `scripts/canonical-verify.sh`
- `scripts/verify-verification-topology.php`
- `scripts/verify-performance-baseline.php`
- `scripts/verify-release-orchestration.php`
- `tests/Architecture/VerificationWorkflowTopologyTest.php`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `scripts/verify-ai-development-protocol.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`
- `app/Console/Commands/SongChartDoctorCommand.php`
- `scripts/compile-repository-contracts.php`
- `scripts/verify-artifact-provenance.php`
- `scripts/package-verified-source.php`
- `scripts/snapshot-postgres-schema.php`
- `scripts/verify-package-upstream-adaptations.php`
- `scripts/record-canonical-verification.php`

- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/domain/model-schema-contracts.json`
- `composer.json`
- `docs/project/engineering/verification-topology.json`
## Allowed incidental files

- exact-tree generated repository contract manifest;
- formatter-only changes to listed PHP files;
- installer/package metadata outside the repository.

## Scope deviations

None planned.

## Security, authorization, and data impact

None. The stage only changes engineering governance and verification routing.

## Tests and verification

Focused:
- `php scripts/compile-repository-contracts.php --refresh-check`
- `composer repository-compiler:verify`
- Architecture `ExecutableRepositoryAuthorityTest`
- authority dependency / impact / AI / topology / code-generation guards.

Candidate:
- `composer stage:verify`.

Canonical:
- `songchart verify`.

Packaging:
- pre-canonical delivery is changeset-only because the v24.3 migration-history baseline is target-sealed;
- only `composer release:package` from the exact canonical target after provenance PASS may produce the authoritative full-source artifact.

## Rollback

File-only rollback. No database or dependency rollback.

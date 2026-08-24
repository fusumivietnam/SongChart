# Stage 16.5.5 — Verification Surface Reduction

Status: implementation candidate.

## Goal

Reduce the active verification/release command surface to one clear workflow while preserving existing static, PostgreSQL, runtime, canonical and provenance coverage.

## Non-goals

- weakening or deleting behavioral coverage;
- changing product/domain behavior;
- caching verification evidence;
- rewriting frozen historical stage documents merely to replace old command names;
- changing database schema or dependencies.

## Acceptance criteria

- Active Composer aliases `verify`, `release:verify`, `test:all`, `test:postgres-clean`, `release-contract:verify`, and `delivery:verify` are removed.
- Active workflow entrypoints are bounded by `verification-command-surface.json`.
- Candidate closure remains `composer stage:verify`.
- Canonical closure remains `songchart verify` / `composer canonical:verify`.
- Release source packaging remains `composer release:package` after canonical/provenance PASS.
- Historical Stage 11 release/export scripts are removed from active `scripts/`.
- Active docs and AI protocol reference only current entrypoints.
- Historical documentation may retain historical commands as history.
- Machine guards prevent removed aliases/orchestration from returning.

## Affected modules and boundaries

Engineering/tooling governance only: Composer command surface, release/verification contracts, active engineering documentation, architecture tests and verifier ownership.

## Changed authorities

- `verification-command-surface`
- `verification-topology`
- `ai-development-protocol`
- `executable-repository-authority`

## Expected files

- `composer.json`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/regression-ledger.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/package-registry.json`
- active testing/release documentation
- verification/repository/database/foundation guard scripts
- Architecture tests consuming legacy aliases
- `tests/Architecture/VerificationCommandSurfaceTest.php`
- `candidate-verification.json`
- current history/index/validation records.

- `scripts/canonical-verify.sh`
- `scripts/verify-performance-baseline.php`
- `scripts/verify-release-orchestration.php`
- `tests/Architecture/VerificationWorkflowTopologyTest.php`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `scripts/verify-ai-development-protocol.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`
- `docs/project/generated/repository-contract-manifest.json`
- `docs/project/stack/database-test-contract.json`
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
- `scripts/verify-verification-command-surface.php`
- `scripts/verify-verification-topology.php`

- `scripts/verify-runtime-authority-closure.php`
- `tests/Architecture/ContractCoverageReleaseBaselineTest.php`
## Removed active files

- `scripts/export-release-baseline.ps1`
- `scripts/finalize-stage-11-release.ps1`
- `scripts/finalize-stage-11-release.bat`

Git and frozen historical documentation preserve their history.

## Allowed incidental files

- exact-tree generated repository contract manifest;
- formatter-only changes to listed files;
- changeset/release metadata outside the repository.

## Scope deviations

None planned.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/governance/authority-dependencies.json`

### Installed versions

No dependency change. Existing locked PHP 8.5 / Laravel 13 / PostgreSQL 18 / Node 22 authorities remain unchanged.

### Official external sources

No new external package/API/runtime capability is introduced. This stage only consolidates repository-owned Composer/Docker workflow commands.

### Native capability assessment

Composer scripts remain the native command adapters; Docker remains the runtime authority; existing Laravel/Pest/PHPStan/Pint tooling remains unchanged. No additional package is required.

### Custom implementation justification

Custom code is limited to SongChart-specific machine-readable command-surface governance and verification because Composer does not provide project-specific authority/deprecation semantics.

## Security, authorization, and data impact

None. No application route, privilege, provider data or persistent schema changes.

## Verification plan

- Focused: command-surface verifier, topology verifier, repository compiler, Architecture command-surface tests.
- Candidate closure: `composer stage:verify`.
- Canonical closure: `songchart verify`.
- Packaging: `composer release:package`.
- No removed alias may be used as an intermediate workaround.


## Tests and verification

- `php scripts/verify-verification-command-surface.php`
- `php scripts/verify-verification-topology.php`
- repository compiler/regression/candidate/authority dependency/impact-map guards
- Architecture command-surface/topology tests
- target `composer stage:verify`
- Docker canonical `songchart verify`
- checks unavailable in packaging environment are explicitly not claimed

## Rollback

File-only rollback. No database/dependency rollback.


## Authority dependency closure

Registered dependents reconciled or explicitly reviewed for this stage:

### verification-command-surface
- `composer.json`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `scripts/verify-verification-command-surface.php`
- `scripts/verify-verification-topology.php`

### verification-topology
- `scripts/canonical-verify.sh`
- `scripts/verify-verification-topology.php`
- `scripts/verify-performance-baseline.php`
- `scripts/verify-release-orchestration.php`
- `tests/Architecture/VerificationWorkflowTopologyTest.php`

### ai-development-protocol
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `PROJECT_AUTHORITY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `scripts/verify-ai-development-protocol.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`

### executable-repository-authority
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

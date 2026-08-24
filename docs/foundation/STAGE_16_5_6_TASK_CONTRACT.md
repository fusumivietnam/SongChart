# Stage 16.5.6 — Migration Lifecycle & Upgrade Safety

Status: implementation candidate.

## Goal

Close the gap between fresh-schema verification and real persistent-database upgrades. Historical migrations become immutable, known schema drift is repaired through forward-only migrations, and canonical verification proves a supported previous-schema → current PostgreSQL upgrade.

## Non-goals

- changing SongChart product behavior;
- changing authorization or provider semantics;
- adopting a migration package;
- introducing SQLite release authority;
- general destructive migration automation;
- deployment orchestration beyond migration verification.

## Acceptance criteria

- every migration that existed before Stage 16.5.6 is fingerprinted in `migration-lifecycle-contract.json`;
- frozen migration fingerprints fail verification when edited;
- the Activitylog historical create migration no longer contains the later `attribute_changes` repair;
- a new guarded forward migration adds `attribute_changes` only when needed;
- fresh PostgreSQL install still materializes the full current package schema;
- canonical verification reconstructs the supported pre-correction schema state and runs Laravel `migrate` forward;
- the upgrade lane proves the target column and migration ledger entry exist after upgrade;
- a second `migrate` pass succeeds idempotently;
- package schema contract records historical and forward migration ownership;
- AI/developer documentation explicitly forbids editing frozen migration history.

## Affected modules and boundaries

Engineering/database migration lifecycle only. No application route or domain behavior changes.

## Changed authorities

- `migration-lifecycle`
- `verification-topology`
- `ai-development-protocol`
- `executable-repository-authority`

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/stack/migration-lifecycle-contract.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/stack/release-pipeline-contract.json`

### Installed versions

No dependency change. Existing PHP 8.5, Laravel 13, PostgreSQL 18, Spatie Activitylog v5 and Node 22 authorities remain unchanged.

### Official external sources

No new dependency or external API is introduced. The implementation uses Laravel migrations/Schema APIs already owned by the repository.

### Native capability assessment

Laravel migrations and Schema facade already provide the required forward migration and table/column existence checks. PostgreSQL 18 remains the release database authority. No third-party migration framework is justified.

### Custom implementation justification

SongChart requires repository-specific immutable-history fingerprints and an isolated previous-schema fixture because Laravel itself does not know which committed historical migrations have already become release-frozen for this project.

## Expected files

- `database/migrations/2026_08_13_000100_create_activity_log_table.php`
- `database/migrations/2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing.php`
- `docs/project/stack/migration-lifecycle-contract.json`
- `docs/project/stack/package-schema-contracts.json`
- `scripts/verify-migration-lifecycle.php`
- `scripts/run-migration-upgrade-test.php`
- `tests/Architecture/MigrationLifecycleUpgradeSafetyTest.php`
- `composer.json`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/regression-ledger.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/generated/repository-contract-manifest.json`
- `candidate-verification.json`
- current documentation/history records.

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
- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/runtime-environments.json`
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

- `scripts/verify-privileged-audit.php`
- `tests/Architecture/PrivilegedAuditGovernanceTest.php`
- `app/Support/Engineering/PhpSemanticFingerprint.php`
- `tests/Unit/PhpSemanticFingerprintTest.php`
- `scripts/seal-migration-lifecycle.php`
## Allowed incidental files

- formatter-only changes to listed PHP files;
- exact-tree generated repository contract manifest;
- packaging metadata outside the source repository.

## Scope deviations

None planned.

## Security, authorization, and data impact

The runtime upgrade lane is destructive only against the isolated PostgreSQL verification database and must pass `verify-test-database-safety.php` before schema reset. No development or production database may be used by the lane.

## Tests and verification

Focused:
- `composer migration-lifecycle:verify`
- migration lifecycle Architecture test
- package/repository contract verifiers

Candidate:
- `composer stage:verify`

Canonical:
- `songchart verify`
- canonical `@migration-upgrade:verify`
- migration runtime schema contract after the upgrade lane

Packaging:
- `composer release:package` only after canonical/provenance PASS.

## Rollback

Code-only rollback before deployment. Once a forward migration has been applied to persistent data, rollback requires an explicit reviewed migration/data rollback plan rather than editing historical migrations.

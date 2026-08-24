# Stage 16.5.4 Task Contract — Delivery Verification Authority Hotfix

Status: corrective implementation contract.

## Goal

Correct the Stage 16.5.3 release exporter so staged source delivery verification uses the Composer `delivery:verify` authority declared by the repository, eliminating a parallel direct invocation of `verify-source-package.php --delivery` and restoring agreement with the architecture guardrail.

## Non-goals

- No runtime application behavior change.
- No migration, schema or persistent data change.
- No authentication or authorization change.
- No provider integration or provider-operation feature change.
- No dependency update and no fabricated lockfile.
- No lowering, ignoring or baselining of Pint, Larastan, PHPStan, SQLite, PostgreSQL or release gates.

## Acceptance criteria

- `scripts/export-release-baseline.ps1` runs `composer release:verify` before staging.
- Staged source is verified by `composer delivery:verify` from within the staging directory.
- The exporter fails closed when the staged delivery verification command fails.
- `ContractCoverageReleaseBaselineTest` passes without weakening its delivery-authority assertion.
- Current-stage documentation/history records Stage 16.5.4 consistently.
- No runtime/schema files are changed.

## Affected modules and boundaries

- Release export/delivery verification tooling.
- Architecture guardrail evidence.
- Current-stage governance documentation.

## Expected files

- `README.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_5_4_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_5_4_VALIDATION_REPORT.md`
- `scripts/export-release-baseline.ps1`

## Allowed incidental files

- Changeset installer, manifest, README and rollback notes.
- Backup metadata under `.changeset-backups/` on the target machine.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/RELEASE_BASELINE_STATUS.md`
- `composer.json`
- `tests/Architecture/ContractCoverageReleaseBaselineTest.php`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.3` | `composer.json` |
| Laravel | `^13.0` | `composer.json`; target lockfile when present |
| Composer | Composer 2 | target development machine |
| PowerShell | target Windows environment | target development machine |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Composer | `https://getcomposer.org/doc/articles/scripts.md` | Composer script execution as repository command authority | 2026-08-08 |
| Microsoft | `https://learn.microsoft.com/powershell/module/microsoft.powershell.core/push-location` | scoped staging-directory execution and location restoration | 2026-08-08 |

### Native capability assessment

- Composer already owns the `delivery:verify` script and is the correct repository command boundary.
- PowerShell `Push-Location`/`Pop-Location` is sufficient to execute that Composer script against the clean staging tree.
- No additional package or custom process runner is required.

### Custom implementation justification

- The custom exporter remains necessary to build a clean source staging tree and ZIP archive after release verification.
- The exporter must delegate delivery validation to the existing Composer authority instead of duplicating its underlying PHP command.

## Domain contract and use-case data surface

No domain entity, operational surface, route, DTO or persistence contract changes.

## Security, authorization, and data impact

No application security or data mutation impact. The change reduces verification-path divergence in release packaging.

## Tests and verification

- PHP syntax for repository PHP files touched indirectly by packaging: none changed.
- `php scripts/verify-documentation.php`.
- `php scripts/verify-official-sources.php`.
- `php scripts/verify-domain-contracts.php`.
- `php scripts/verify-operational-contracts.php`.
- `php scripts/verify-use-case-contracts.php`.
- `php scripts/verify-type-guardrails.php`.
- `php scripts/verify-impact-test-map.php`.
- `php scripts/verify-repository-state.php`.
- Focused `tests/Architecture/ContractCoverageReleaseBaselineTest.php` on the target machine.
- Full `composer release:verify` on the target machine.

## Documentation impact

Update current-stage pointer, documentation index and development history only.

## Rollback

Restore the exporter and documentation files from the changeset backup. No database rollback is required.

# Stage 16.7.3 — Target Pint Auto-Repair Hotfix — Task Contract

Status: corrective task contract.

## Goal

Resolve the remaining `class_attributes_separation` violation in `app/Models/User.php` by executing the repository-installed Laravel Pint fixer on the target machine, then re-running focused and full verification.

## Non-goals

- No feature, route, schema, authorization, provider, identity-decision, or database behavior change.
- No Pint rule suppression, exclusion, baseline, or custom formatter.

## Expected files

- `app/Models/User.php` (formatter-produced target change)
- `README.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_7_3_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_3_VALIDATION_REPORT.md`

## Allowed incidental files

- changeset installer, manifest, rollback, and backup metadata.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `pint.json`
- `composer.json`
- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel Pint | `^1.24` | `composer.json`; target-machine `composer.lock` |
| PHP/Laravel dependencies | target-machine resolved versions | `composer.lock` |

### Official external sources

- Laravel Pint official documentation.

### Native capability assessment

Laravel Pint is the native repository formatting authority and can deterministically repair the reported style violation.

### Custom implementation justification

No custom formatting algorithm is introduced. The installer invokes the target-installed Pint binary in fix mode and then verifies with `--test`.

## Domain contract and use-case data surface

Unchanged.

## Security, authorization, and data impact

No behavior or data impact.

## Tests and verification

- Run Pint fix on `app/Models/User.php` using target `vendor/bin/pint`.
- Run focused Pint `--test` on the six Stage 16.7 PHP files.
- Run repository contract/governance verifiers.
- Run focused Stage 16.6/16.7 tests.
- Run `composer release:verify`.

## Documentation impact

Current-stage metadata and corrective history only.

## Rollback

Restore `User.php` and overwritten documentation from `.changeset-backups/stage-16.7.3-<timestamp>/` and remove files listed in `NEW_FILES.txt`.

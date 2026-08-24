# Stage 16.7.4 — Backed Enum Read-Model Normalization Hotfix — Task Contract

Status: corrective task contract.

## Goal

Prevent the Identity Conflict Review admin read model from casting Laravel/Eloquent backed-enum attributes directly to string. Normalize enum-backed `verification_state` and `match_status` values to scalar strings before they reach the view.

## Non-goals

- No schema, migration, route, authorization, provider, or identity-decision behavior change.
- No enum cast removal from canonical models.
- No view-side workaround that hides object/scalar mismatch.

## Expected files

- `app/Support/Admin/IdentityConflictReviewConsole.php`
- `tests/Feature/IdentityConflictReviewUiTest.php`
- `README.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_7_4_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_4_VALIDATION_REPORT.md`

## Allowed incidental files

- changeset installer, manifest, rollback, and backup metadata.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `app/Domain/Catalog/Enums/VerificationState.php`
- `app/Domain/Catalog/Enums/MatchStatus.php`
- canonical catalog model casts
- `app/Support/Admin/IdentityConflictReviewConsole.php`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `>=8.3` | `composer.json` |
| Laravel | repository-resolved target version | `composer.lock` on target |
| Laravel Pint | repository-resolved target version | `composer.lock` on target |

### Official external sources

- PHP backed-enum semantics and Laravel Eloquent enum casting behavior are consumed through repository-native language/framework features; no external provider API is involved.

### Native capability assessment

PHP backed enums expose their scalar representation through `BackedEnum::value`. The correct presentation boundary is to read that scalar value rather than cast the enum object itself.

### Custom implementation justification

A small private scalar-normalization helper is required because the console read model combines enum-backed and scalar Eloquent attributes into a view-oriented array. No custom enum system is introduced.

## Domain contract and use-case data surface

No fields are added or removed. Existing `verification_state` and entity-match `status` fields retain their declared contracts; only their read-model representation changes from enum objects to scalar strings.

## Security, authorization, and data impact

None. Read-only presenter normalization only.

## Tests and verification

- PHP syntax on changed files.
- Focused Pint `--test` on the console and regression test.
- Existing domain/operational/use-case/type/repository/provider/identity UI verifiers.
- Focused `IdentityConflictReviewUiTest`.
- Full `composer release:verify` on Laragon.

## Documentation impact

Current-stage metadata and corrective history only.

## Rollback

Restore overwritten files from `.changeset-backups/stage-16.7.4-<timestamp>/` and remove files listed in `NEW_FILES.txt`.

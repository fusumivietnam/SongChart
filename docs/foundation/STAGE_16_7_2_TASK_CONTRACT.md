# Stage 16.7.2 — Pint Style Conformance Hotfix — Task Contract

Status: corrective task contract.

## Goal

Bring the Stage 16.7.1 changed PHP surface into the repository Pint/Laravel style contract so focused formatting verification passes before Larastan and runtime tests.

## Non-goals

- No feature, route, schema, authorization, provider, identity-decision, or database behavior change.
- No Pint rule suppression, exclusion, baseline, or style-policy weakening.

## Acceptance criteria

- The six files reported by target-machine Pint are formatted according to the existing `pint.json` Laravel preset plus strict-types/import-order rules.
- PHP syntax remains valid.
- Provider Operations and Identity Conflict Review static verifiers remain green.
- Target-machine focused Pint and full `composer release:verify` are rerun.

## Affected modules and boundaries

Formatting-only correction across controllers, user model, application provider, provider operations support service, and routes. Repository stage/history/index metadata is updated for traceability.

## Expected files

- `app/Http/Controllers/Admin/IdentityConflictReviewController.php`
- `app/Http/Controllers/Admin/OperationsController.php`
- `app/Models/User.php`
- `app/Providers/AppServiceProvider.php`
- `app/Support/Admin/ProviderOperationsConsole.php`
- `routes/web.php`
- `README.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_7_2_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_2_VALIDATION_REPORT.md`

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
- `docs/foundation/STAGE_16_7_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_1_TASK_CONTRACT.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel Pint | `^1.24` | `composer.json`; target-machine `composer.lock` when present |
| PHP/Laravel dependencies | target-machine resolved versions | `composer.lock` |

### Official external sources

- Laravel Pint official documentation and the repository-selected Laravel preset semantics.

### Native capability assessment

- Capability owner: Laravel Pint.
- Native/first-party capability available: yes.
- Selected primitive: existing `php vendor/bin/pint` quality lane.
- Why it satisfies the requirement: formatting violations are deterministic and should be corrected by the repository formatter rather than custom style rules.

### Custom implementation justification

No custom formatter is introduced. The hotfix only commits the formatting result and keeps Pint as the authority.

## Domain contract and use-case data surface

- Actors, routes, identifiers, canonical/operational reads, writes, null semantics, and DTO contracts are unchanged.
- `domain-contracts.json`, `operational-contracts.json`, and `use-case-contracts.json` are unchanged.

## Security, authorization, and data impact

No security, authorization, data mutation, retention, provider-policy, or database change.

## Tests and verification

- PHP syntax on all six corrected PHP files.
- `php scripts/verify-provider-operations-console.php`.
- `php scripts/verify-identity-conflict-review-ui.php`.
- repository documentation/domain/operational/use-case/type/impact/state verifiers.
- target machine: focused `php vendor/bin/pint --test` on the six files.
- target machine: `composer release:verify`.

## Documentation impact

Update current stage, development history, documentation index, task contract, validation report, and changeset delivery notes.

## Rollback

Restore overwritten files from `.changeset-backups/stage-16.7.2-<timestamp>/` and remove files listed in `NEW_FILES.txt`.

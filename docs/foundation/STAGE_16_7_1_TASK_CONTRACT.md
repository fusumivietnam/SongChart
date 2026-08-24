# Stage 16.7.1 — Baseline Preflight & Provider Operations Repair — Task Contract

Status: corrective task contract.

## Goal

Make the Stage 16.7 delivery fail before mutation when the target baseline is unsupported, and provide a cumulative repair payload for the Stage 16.6 provider-operations files required by the Stage 16.7 quality gate.

## Non-goals

- No new identity-review behavior.
- No provider mutation, retry, cancel, requeue, enable, or disable workflow.
- No migration, schema, authentication, or provider API change.

## Acceptance criteria

- A clean Stage 16.6 target can apply the changeset.
- A target partially modified by the failed Stage 16.7 changeset can be repaired without manually identifying missing Stage 16.6 files.
- Unsupported older baselines are rejected before payload copying.
- `composer provider-operations:verify` remains enabled and passes against the repaired source.
- Stage 16.7 identity-conflict contracts and UI remain intact.

## Affected modules and boundaries

- Changeset delivery/preflight.
- Stage 16.6 provider-operations support files and verification evidence.
- Repository current-stage/history/index metadata.
- No runtime product behavior beyond restoring the intended 16.6 + 16.7 cumulative source state.

## Expected files

- `app/Http/Controllers/Admin/OperationsController.php`
- `app/Support/Admin/ProviderOperationsConsole.php`
- `resources/views/admin/operations/**`
- `routes/web.php`
- `scripts/verify-provider-operations-console.php`
- `tests/Feature/ProviderOperationsConsoleTest.php`
- Stage 16.6 operational/use-case contracts and governance records
- Stage 16.7 identity-review files already delivered by the parent stage
- `docs/foundation/STAGE_16_7_1_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_1_VALIDATION_REPORT.md`

## Allowed incidental files

- `README.md`
- `composer.json`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- changeset installer/manifest/rollback metadata

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/foundation/STAGE_16_6_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_7_TASK_CONTRACT.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel / PHP dependencies | Target-machine resolved versions | `composer.lock` |
| Frontend dependencies | Target-machine resolved versions | `package-lock.json` |

### Official external sources

No new external API or framework capability is introduced by this delivery repair.

### Native capability assessment

- Capability owner: repository changeset delivery.
- Native/first-party capability available: partial.
- Selected primitive: PowerShell filesystem checks, existing Composer verification scripts, existing Stage 16.6/16.7 repository authorities.
- Why it satisfies the requirement: preflight can reject unsupported baselines before any file copy, while cumulative payload restores required project files deterministically.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: the project-specific staged changeset format has no framework-native baseline preflight.
- Narrow custom boundary: changeset installer validation and cumulative repair contents.
- Framework primitives reused: existing Composer/PHP verification commands.
- Non-goals: no application-domain mutation.

## Domain contract and use-case data surface

- Actor and preconditions: developer applying a repository changeset to Stage 16.6 or a partially applied Stage 16.7 target.
- Input types and identifier formats: filesystem project root and README stage marker.
- Exact entity fields read: none.
- Exact entity fields written: none.
- Null/unknown semantics: unsupported or unresolvable baseline fails before copy.
- Output DTO/presentation contract: installer success/failure output only.
- Route/API contract: unchanged.
- Relationship invariants: Stage 16.6 provider operations and Stage 16.7 identity review contracts remain cumulative.
- Contract changes required: no domain-schema change.
- `domain-contracts.json` entries affected: none.

## Security, authorization, and data impact

No authentication, authorization, sensitive-data, retention, provider-policy, or database behavior changes.

## Tests and verification

- `php scripts/verify-provider-operations-console.php`
- `php scripts/verify-identity-conflict-review-ui.php`
- `php artisan test tests/Feature/ProviderOperationsConsoleTest.php`
- `php artisan test tests/Architecture/IdentityConflictReviewUiArchitectureTest.php tests/Feature/IdentityConflictReviewUiTest.php`
- `composer release:verify` on the target machine.
- Packaging environment does not claim target-machine Pint, Larastan, SQLite, PostgreSQL, or full release success.

## Documentation impact

Update current stage, development history, documentation index, task contract, validation report, and changeset delivery notes.

## Rollback

Restore overwritten files from `.changeset-backups/stage-16.7.1-<timestamp>/` and remove files listed in `NEW_FILES.txt`.

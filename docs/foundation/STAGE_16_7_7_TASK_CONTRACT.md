# Stage 16.7.7 — Identity Conflict UI Assertion Contract Hotfix — Task Contract

## Authority and official sources

### Repository authorities
- `docs/foundation/STAGE_16_7_TASK_CONTRACT.md`
- `docs/project/domain/use-case-contracts.json`
- `tests/Feature/IdentityConflictReviewUiTest.php`

### Installed versions
Use the target development machine's installed Laravel, Pest and Pint versions.

### Official external sources
No new external capability is introduced.

### Native capability assessment
The existing feature test already verifies the required Identity Conflict Review UI outputs. No framework change is needed.

### Custom implementation justification
A Stage 16.7.4 regression assertion incorrectly required the literal `unverified` string even though Stage 16.7 does not declare canonical verification state as a required UI output. The correction removes only that assertion.

## Use case
Restore the feature test to the Stage 16.7 contract: candidate labels, match status, provider evidence and decision history are required; canonical verification state is not a mandatory rendered field.

## Data surface
No reads or writes change.

## Routes
No route changes.

## Security invariants
No authorization or middleware changes.

## Expected files
- `tests/Feature/IdentityConflictReviewUiTest.php`
- current-stage/history/documentation metadata

## Allowed incidental files
- changeset installer, manifest and rollback notes

## Scope deviations
None. Runtime presenter normalization from Stage 16.7.4 remains intact.

## Tests and verification
- Pint test for `tests/Feature/IdentityConflictReviewUiTest.php`
- `php artisan test tests/Feature/IdentityConflictReviewUiTest.php`
- repository/static verifiers
- `composer release:verify` on target

# Stage 16.7 — Identity Conflict Review UI — Task Contract

## Authority and official sources

### Repository authorities
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/providers/EXACT_IDENTITY_RESOLUTION.md`
- `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`

### Installed versions
Use versions resolved on the target development machine; do not infer framework/package behavior beyond repository declarations and installed dependencies.

### Official external sources
No new external provider API is integrated in Stage 16.7. Laravel routing, authorization gates, validation, password-confirmation middleware, Eloquent transactions and Blade are native capabilities used by this stage.

### Native capability assessment
The existing `IdentityConflictReviewService` already owns transactional conflict decisions and immutable decision snapshots. Laravel authorization/validation/password confirmation are sufficient to expose that service safely in the admin UI.

### Custom implementation justification
The review console is SongChart-specific because candidates, provider evidence, canonical entity types and decision semantics are domain-specific. No parallel identity-resolution engine is introduced.

## Use case
Administrators may inspect the conflict queue and detail evidence. Only roles with the dedicated `manage-identity-conflicts` capability may record an explicit decision, with recent password confirmation and a required rationale.

## Data surface
Reads and writes are declared exactly in `use-case-contracts.json`. Operational writes are limited to identity review status, immutable decision history and entity-match state already owned by `IdentityConflictReviewService`.

## Routes
- `GET /admin/identity-conflicts`
- `GET /admin/identity-conflicts/{review}`
- `POST /admin/identity-conflicts/{review}/decisions`

## Security invariants
- canonical admin middleware remains mandatory;
- decision mutation additionally requires `can:manage-identity-conflicts` and `password.confirm`;
- rationale is mandatory;
- approve/reject require a candidate from the review;
- resolved reviews remain immutable until an explicit reopen decision;
- controller mutation must go through `IdentityConflictReviewService`.

## Expected files
- `app/Support/Admin/IdentityConflictReviewConsole.php`
- `app/Http/Controllers/Admin/IdentityConflictReviewController.php`
- `resources/views/admin/identity-conflicts/**`
- `app/Enums/UserRole.php`
- `app/Models/User.php`
- `app/Providers/AppServiceProvider.php`
- `routes/web.php`
- `docs/project/domain/*.json`
- `tests/Feature/IdentityConflictReviewUiTest.php`
- `scripts/verify-identity-conflict-review-ui.php`

## Allowed incidental files
- Composer verification registration
- current-stage/history/documentation index/roadmap metadata
- use-case verifier extension for operational write surfaces

## Scope deviations
None intended. Stage 16.7 does not add provider retry/cancel/requeue or provider enable/disable operations; those remain Stage 16.8.

## Tests and verification
- `composer identity-conflict-ui:verify`
- `composer operational-contracts:verify`
- `composer use-case-contracts:verify`
- `php artisan test tests/Feature/IdentityConflictReviewUiTest.php`
- `composer release:verify` on the target development machine

# Stage 16.7.9 — Larastan Identity Read-Model Type Precision Hotfix — Task Contract

## Authority and official sources

### Repository authorities
- `app/Models/Providers/Identity/IdentityConflictReview.php` cast definitions
- `app/Support/Admin/IdentityConflictReviewConsole.php` read-model boundary
- `phpstan.neon` and Composer quality/release scripts
- target machine `vendor/bin/phpstan` and `vendor/bin/pint`

### Installed versions
Use the target development machine's installed PHP, Laravel, Larastan/PHPStan, and Pint versions. No static-analysis rule is relaxed.

### Official external sources
No new external capability is introduced.

### Native capability assessment
Eloquent exposes raw persisted attributes through `getRawOriginal()`, while backed enums own conversion from their persisted scalar values. PHPStan already understands list return types and should not be bypassed with redundant `array_values()` calls.

### Custom implementation justification
No custom cast or static-analysis suppression is introduced. The correction makes the presenter boundary explicit: derive `EntityType` from the raw scalar and preserve the already-known candidate ID list shape.

## Use case
Remove two Larastan errors in `IdentityConflictReviewConsole` without changing review behavior or weakening static analysis.

## Data surface
Reads the same `identity_conflict_reviews.entity_type` and `candidate_entity_ids` values. No database writes change.

## Routes
No route behavior changes.

## Security invariants
No authentication, authorization, middleware, or privileged-operation behavior changes.

## Expected files
- `app/Support/Admin/IdentityConflictReviewConsole.php`
- current-stage/history/documentation metadata

## Allowed incidental files
- changeset installer, patch helper, manifest, rollback notes, and target backup records

## Scope deviations
The runtime file is patched in place on the target so previously target-Pint-formatted output is preserved; Pint is run again after the semantic patch.

## Tests and verification
- PHP syntax on the patched presenter
- focused Pint `--test`
- focused `IdentityConflictReviewUiTest`
- repository/static contract and governance verifiers
- full `composer release:verify` on the target machine

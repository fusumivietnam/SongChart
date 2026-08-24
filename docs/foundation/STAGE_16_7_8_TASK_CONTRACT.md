# Stage 16.7.8 — Repository-Wide Pint Closure Hotfix — Task Contract

## Authority and official sources

### Repository authorities
- `pint.json`
- `composer.json` quality and release scripts
- Stage 16.7 Identity Conflict Review UI task contract
- target machine `vendor/bin/pint`

### Installed versions
Use the target development machine's installed PHP and Laravel Pint versions. The target formatter is authoritative for style output.

### Official external sources
No new external capability is introduced.

### Native capability assessment
Laravel Pint already owns repository formatting. The correct remediation is to run the installed formatter against the exact files reported by the full release gate and then verify them with `--test`.

### Custom implementation justification
No custom formatter or rule override is introduced. This hotfix only orchestrates backup, target Pint repair, verification, metadata reconciliation, focused tests, and the existing full release gate.

## Use case
Close residual repository-wide formatting debt exposed by `composer release:verify` after Stage 16.7.7 without altering application semantics.

## Data surface
No database reads or writes change.

## Routes
No route behavior changes.

## Security invariants
No authentication, authorization, middleware, or privileged-operation behavior changes.

## Expected files
- `app/Support/Admin/AdminInformationArchitecture.php`
- `app/Support/DomainContracts/DTO/SupportDataSurface.php`
- `scripts/verify-identity-conflict-review-ui.php`
- `scripts/verify-type-guardrails.php`
- `scripts/verify-use-case-contracts.php`
- `tests/Feature/ProviderOperationsConsoleTest.php`
- current-stage/history/documentation metadata

## Allowed incidental files
- changeset installer, manifest, rollback notes, and target backup records

## Scope deviations
The six PHP files are formatted on the target machine instead of being replaced from packaging output so the installed Pint version remains the formatting authority.

## Tests and verification
- Pint fix on the six reported files
- Pint `--test` on the same files
- repository/static contract and governance verifiers
- focused Provider Operations and Identity Conflict Review tests
- full `composer release:verify` on the target machine

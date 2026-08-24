# Stage 16.8 — Provider Mutation & Recovery Controls

## Authority and official sources
### Repository authorities
Domain, operational, use-case and schema ownership contracts remain authoritative.
### Installed versions
Laravel 13 / PHP 8.3 baseline.
### Official external sources
Laravel authorization, validation, database transaction and queue capabilities.
### Native capability assessment
Native gates, password confirmation, transactions, row locks, validation and queues are used.
### Custom implementation justification
SongChart requires domain-specific provider lifecycle and import-recovery invariants plus immutable operational audit evidence.

## Scope
Provider enable/disable/retire, import retry/resume/cancel, idempotency, row locking, authorization, password confirmation and audit ledger. Physical provider deletion is out of scope.

## Expected files
- app/Support/Providers/Operations/**
- app/Http/Controllers/Admin/ProviderMutationController.php
- database/migrations/2026_08_10_000100_create_provider_operation_audits_table.php
- routes/web.php
- resources/views/admin/operations/**
- tests/Feature/ProviderMutationRecoveryControlsTest.php
- scripts/verify-provider-mutation-recovery.php
- docs/project/domain/operational-contracts.json
- docs/project/domain/schema-ownership.json
- composer.json

## Allowed incidental files
- README.md
- docs/DOCUMENTATION_INDEX.md
- docs/project/DEVELOPMENT_HISTORY.md

## Tests and verification
Run provider mutation verifier, Pint, Larastan, isolated SQLite/PostgreSQL tests and release verification.

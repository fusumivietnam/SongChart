# Stage 12.1 Task Contract — Catalog Closure & Validation Hardening

## Goal

Close Stage 12 by hardening provider-match and metadata-conflict invariants, expanding catalog regression verification, and defining the runtime evidence required before Stage 13.

## Scope

- One active `matched` canonical identity per provider entity.
- Symmetric and self metadata-conflict prevention.
- Constraint, deletion behavior and deterministic fixture tests.
- Migration rollback and SQLite/PostgreSQL validation requirements.
- Expanded `composer catalog:verify` coverage.

## Non-goals

- No provider API calls.
- No ingestion jobs or payload storage.
- No automated entity matching.
- No catalog administration UI.

## Acceptance criteria

- Candidate matches may coexist, but only one `matched` row is active per provider entity.
- Metadata conflicts treat A/B and B/A as the same pair and reject A/A.
- Catalog verifier covers all Stage 12 migrations, tables, major unique indexes, models, tests and seeder registration.
- SQLite tests, migration rollback, PostgreSQL CI and full `composer release:verify` are required for closure.

# Stage 13.2 Task Contract

## Goal

Persist every provider import execution, transport request, raw payload, processing item, failure and checkpoint in an auditable and replayable ledger.

## Deliverables

- Six PostgreSQL-compatible ledger tables with ULID keys and indexes.
- Eloquent models and relationships.
- Immutable raw payload boundary.
- Recursive sensitive-data redaction utility.
- SQLite and PostgreSQL feature coverage.
- Executable provider-import-ledger verifier.

## Non-goals

No live provider HTTP adapter, queue orchestration, normalization, identity matching, canonical mutation, admin UI or retention scheduler.

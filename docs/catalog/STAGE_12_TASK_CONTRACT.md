# Stage 12 Task Contract — Canonical Identity & Catalog Data Model

## Goal

Establish the canonical catalog, metadata provenance, provider identity mapping, constraints, deterministic fixtures and validation gates required before provider ingestion.

## Non-goals

- No live provider API calls.
- No bulk import pipeline.
- No search-index replacement.
- No merge/split administration UI.
- No provider ID as an internal primary key.

## Acceptance criteria

- Canonical entities use internal ULIDs.
- Works, recordings, releases and versions remain distinct concepts.
- Provider identities map through explicit match records.
- Field assertions retain source, observation time, confidence and verification state.
- Duplicate-sensitive fields and positions have database constraints.
- Factories and deterministic fixtures cover canonical, provenance, conflict and provider-match scenarios.
- SQLite and PostgreSQL-compatible migrations include rollback.
- `composer catalog:verify` and catalog tests protect the model.

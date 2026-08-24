# Stage 13.1 Task Contract

## Goal

Create stable, provider-neutral contracts for real catalog ingestion without enabling live imports.

## Deliverables

- Catalog adapter and registry contracts.
- Immutable import context, page, payload, normalized entity and failure DTOs.
- Capability and failure enums.
- Rate-limit value object.
- Tagged in-memory registry and null test adapter.
- Unit/Architecture coverage and executable verifier.

## Exit criteria

- No schema or dependency changes.
- No HTTP or canonical mutation implementation.
- Provider catalog verifier, Pint, Larastan, SQLite and PostgreSQL release lanes pass.

# Stage 12.3 — Architecture Conformance & Production Boundary Cleanup

## Scope

- Bind public search to the canonical Eloquent catalog in production.
- Restrict deterministic demo search to explicit local/testing configuration.
- Centralize catalog entity types and provider sync states as enums.
- Add a catalog entity resolver for polymorphic boundaries.
- Remove redundant route middleware, dead preview links and setup dependency drift.
- Add automated conformance verification.

## Exclusions

No provider adapter, remote API call, import pipeline, package addition or schema migration.

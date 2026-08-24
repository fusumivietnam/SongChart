# Stage 16.1 — Unified Entity Rule Engine — Validation Report

## Delivered

Stage 16.1 adds a typed executable Discovery rule engine over canonical entity snapshots, a fail-closed canonical field registry, cross-entity Eloquent-to-snapshot mapping, deterministic sorting and service-container bindings.

## Safety properties

- Provider-neutral: no provider contracts, models, APIs or payload fields are referenced.
- Canonical read-only: the mapper reads catalog entities; the engine cannot mutate them.
- No raw SQL: rule compilation produces PHP predicates only.
- Fail closed: unknown fields, unsupported operators, malformed values, mixed entity sorting and entity-type mismatches throw validation exceptions.
- Deterministic: sorting appends `id ASC` when necessary.
- Stage 17 metrics remain unavailable until their contracts exist.

## Environment note

The packaged source does not vendor Composer dependencies, so the archive build environment can run PHP syntax/static governance verifiers and pure-PHP smoke checks. Full Pest/Larastan/PostgreSQL release gates remain authoritative on the target development environment after applying the changeset.

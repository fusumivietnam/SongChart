# Canonical Mutation Actions

Stage 14.3 introduces the only supported write boundary from validated provider DTOs into the canonical catalog.

## Rules

- Provider adapters never write Eloquent catalog models.
- Every item mutation runs in a database transaction.
- Provider external identity is attached as `provider:<slug>` and makes repeated imports idempotent.
- Existing non-null canonical fields are not overwritten by candidate provider values.
- Every provided field produces a metadata assertion tied to a metadata source.
- Identifiers and relationships use unique catalog constraints and `firstOrCreate` semantics.
- Mutation outcomes are `created`, `updated`, `unchanged`, `skipped`, or `conflict`.

Relationship targets that have not yet been imported are not invented; attachment is deferred until a provider identity exists.

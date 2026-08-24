# Exact Identity Resolution

Stage 15.1 resolves provider entities deterministically before canonical mutation.

Priority order:

1. Existing confirmed `entity_matches` row.
2. Provider-scoped external identifier (`provider:<slug>` + external ID).
3. Exact normalized external identifiers such as ISRC, ISWC, barcode, or provider namespace identifiers.

No name, title, duration, fuzzy, phonetic, or probabilistic matching is allowed in this stage.

Outcomes:

- `matched`: exactly one canonical entity is selected and audited.
- `unmatched`: canonical mutation may create a new entity, then records a confirmed created match.
- `conflict`: multiple canonical entities share exact identifiers; mutation stops and candidate matches are recorded as `needs_review`.

Every decision is persisted in `entity_matches` with match method, confidence, and JSON evidence. Repeated imports reuse the confirmed match and remain idempotent.

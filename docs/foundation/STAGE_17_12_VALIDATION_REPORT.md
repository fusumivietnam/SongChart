# Stage 17.12 — Validation Report

Status: implementation candidate; canonical closure pending.

## Implemented

- Provider/enrichment field evidence can be materialized into `metadata_assertions` after existing evidence admission marks it admissible.
- The existing governed canonical-admission service stages the assertion as a pending canonical decision.
- Canonical entities are not mutated by provider execution or materialization.
- Materialization is idempotent for the same entity/field/source/value fingerprint.
- Enrichment execution records the canonical-admission assertion/decision identifiers in result payload.
- Unsafe materialization falls back to `review_required`.
- Linux host CLI now falls back from Compose `exec` to `run --rm` when the app service is stopped.
- `./songchart candidate` explicitly refreshes generated project context, runs `git diff --check`, and executes candidate verification.
- `./songchart verify` remains read-only/fail-closed.

## Verification status

- Focused Stage 17.12 tests: pending developer Docker execution.
- Pint: pending.
- PHPStan: pending.
- Stage verification: pending.
- Canonical verification: pending.

No closure claim is made until the canonical Docker verification lane passes on the Stage 17.12 target tree.

# Stage 21.0 — Editorial Admin UX

## Purpose

Turn the existing governed editorial/admin surfaces into a clear, safe operator workflow without weakening canonical-admission, provenance, authorization, or audit boundaries.

Stage 21 is a UX/application-surface stage. It must reuse Stage 20 domain/application contracts and must not invent provider-specific canonical identity, bypass canonical admission, or introduce schema changes unless a separately accepted gap proves they are required.

## Product owner

`editor.ingest_and_admit` and `operator.observe_and_recover` from `docs/project/domain/product-user-journeys.json`.

## Invariants

1. Canonical mutation remains owned by the existing governed admission service.
2. Editorial UX must distinguish evidence, queued decisions, applied decisions, and rejected decisions.
3. High-impact actions must state what will happen before submission and remain auditable.
4. Read-only/detail surfaces may improve presentation but must not duplicate mutation semantics.
5. Existing authorization remains authoritative; UI visibility is never treated as authorization.
6. Provider evidence remains provenance/context, not canonical identity.
7. No Stage 21 tranche may silently add schema/domain concepts to solve a presentation problem.

## Tranches

### 21.0A — Admission queue information hierarchy

Status: implementing

Goals:
- Make pending/applied/rejected state immediately legible.
- Separate queued decisions from unstaged evidence.
- Improve value/source/entity presentation without changing read-model ownership.
- Make empty/unavailable states actionable and operator-oriented.

Acceptance:
- Admission index has explicit workflow guidance and state labels.
- Pending work and unstaged evidence are visually/semantically distinct.
- Existing stage/show routes remain the only actions from the index.
- Regression coverage protects the UX contract.

### 21.0B — Decision safety and review context

Status: planned

Goals:
- Improve review context and decision consequences.
- Make apply/reject actions unambiguous.
- Preserve rationale requirement and audit semantics.

### 21.0C — Editorial navigation and accessibility

Status: planned

Goals:
- Normalize editorial navigation vocabulary.
- Improve keyboard/focus/semantic labeling on high-frequency admin flows.
- Keep responsive layouts usable on constrained/mobile operator sessions.

### 21.0D — Stage acceptance and closure

Status: planned

Goals:
- Run impact/canonical verification on exact head.
- Confirm no domain/schema drift was introduced by UX work.
- Close Stage 21.0 only from clean, verified exact-head evidence.

## Explicit non-goals

- Public Stage 23 UX redesign.
- Recommendation/social features.
- New provider adapters.
- New canonical entity types.
- Schema migrations for presentation-only needs.
- Replacing existing authorization, admission, provenance, or audit owners.

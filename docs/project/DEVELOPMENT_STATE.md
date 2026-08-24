# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `17.9.4 — Candidate Stage Consistency Closure`: **canonical Docker verification PASSED** on the developer target tree.
- Development authority remains Docker/Compose through `songchart.bat`.

## Current stage

- `17.10 — Enrichment Orchestrator`
- Candidate: `v5`, in development, not canonically accepted yet.

## Implemented slices

### v1 — deterministic scheduling

- deterministic enrichment schedule builder;
- stable SHA-256 idempotency keys per entity + need + provider;
- duplicate need collapse;
- disabled-provider deferral;
- priority then cost ordering.

### v2 — persistence and database idempotency

- `enrichment_attempts` persistence ledger;
- unique database constraint on the schedule idempotency key;
- race-safe `insertOrIgnore` reservation;
- application boundary through `EnrichmentAttemptStore`.

### v3 — queue dispatch and provider rate gate

- provider-neutral queued `GateEnrichmentAttempt` work;
- existing `ProviderRatePolicyRegistry` and `ProviderRequestGate` reused rather than duplicated;
- ledger state transitions `planned → queued → gating → ready`;
- retryable gate failures persist `deferred` state and release the queue job with bounded delay;
- non-retryable gate failures persist `failed` state;
- no provider HTTP execution or canonical mutation yet.

### v4 — provider execution outcome boundary

- `ExecuteEnrichmentAttempt` runs only after the existing request/rate gate marks an attempt `ready`;
- `EnrichmentExecutor` is the single provider-execution boundary;
- the safe default executor produces `review_required` instead of guessing unsupported provider behavior;
- retryable execution returns to `GateEnrichmentAttempt`, so every retry passes the provider rate gate again;
- successful evidence is persisted to `result_payload`; review outcomes persist `review_reason`;
- execution state is now `ready → executing → succeeded|review_required|rejected|deferred|failed`;
- no canonical entity mutation is performed by the orchestrator.

### v5 — governed MusicBrainz execution, freshness and budget admission

- `GovernedEnrichmentExecutor` routes MusicBrainz attempts through the existing provider catalog adapter and normalization pipeline;
- unsupported providers remain fail-safe `review_required`;
- fresh MusicBrainz identity evidence short-circuits before network execution;
- a configurable UTC daily execution budget can defer provider work without replacing the existing request/rate gate;
- normalized provider candidates are persisted as evidence only; canonical entities are not mutated;
- the superseded `ReviewRequiredEnrichmentExecutor` implementation is removed because its fallback responsibility is now contained in the governed executor.

## Development workflow

Use the single public CLI:

```powershell
.\songchart.bat dev ready
.\songchart.bat dev test tests/Unit/Catalog/BuildEnrichmentScheduleTest.php
.\songchart.bat dev test tests/Feature/Catalog/EnrichmentQueueDispatchTest.php
```

Canonical closure is separate:

```powershell
.\songchart.bat verify
```

## Cleanup status

`docker-dev-ready.bat` and `docker-dev-cycle.bat` are obsolete and must be absent. Their supported behavior lives under `songchart.bat dev ...`. Stage 17.10 v3 apply removes them idempotently and asserts they remain absent. Other historical wrappers stay until repository contracts/reference scans are updated deliberately.

## Next 17.10 slices

1. concrete governed provider executors, starting with MusicBrainz identity enrichment;
2. provider quota/freshness scheduling;
3. canonical mutation admission only through existing governed evidence boundaries;
4. remove additional compatibility wrappers only after executable authority consumers are migrated.

### Stage 17.10 v6 — evidence validation + review admission

- provider-normalized candidates are validated through the existing `NormalizedProviderEntityValidator`;
- successful provider execution is assessed by `EnrichmentEvidenceAdmissionPolicy` before ledger success;
- decisions are explicit: `admissible`, `review_required`, or `rejected`;
- ledger status `succeeded` means admissible evidence only, while invalid/mismatched evidence becomes `rejected`;
- `result_payload.evidence_admission` records the decision and always records `canonical_mutation=false`;
- no canonical entity mutation or automatic evidence promotion occurs in this slice.

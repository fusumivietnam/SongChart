# Stage 22 — Blocker Report

Status: active blocker authority for Stage 22.1 closure.

## What is implemented

- Critical intersection audit contains no unknown production edge.
- Golden provider/canonical cases cover single-provider replay, multi-provider agreement, provider conflict, ambiguous identity and remaster/version semantics.
- Canonical admission emits a semantic `CanonicalEntityChanged` event after governed mutation.
- Active Discovery channels for the changed entity type are queued for bounded reprojection.
- Chart calculation now has a provider-neutral observation DTO, explicit canonical recording identity, observation provenance, a fixed calculation version and deterministic ranking.
- A chart snapshot can be projected back onto the same canonical public recording identity without inventing a parallel public entity.

## Blocker 1 — No production provider metric source

### Evidence

Existing provider integrations expose catalog/enrichment/destination behavior, but the repository does not expose a production time-series metric observation stream suitable for chart ranking. Existing UI authority explicitly forbids fabricated chart/listener/popularity data.

### Why this blocks closure

The golden flow requires real traceable provider/time-series observations. Unit/feature fixtures prove calculation behavior but cannot be accepted as production chart evidence.

### Remediation

Implement Stage 22.2 provider metric ingestion with these minimum fields:

- provider slug;
- provider item identity;
- canonical recording identity after governed resolution;
- metric key and unit;
- observed-at/source timestamp;
- fetched-at timestamp;
- numeric value;
- immutable observation id/fingerprint;
- raw/source provenance pointer.

Do not add a provider until its official API/data terms expose a lawful, stable metric suitable for SongChart charting.

## Blocker 2 — No persisted immutable chart snapshot owner

### Evidence

`BuildChartSnapshot` produces a deterministic provenance-bearing DTO, but the current implementation does not persist immutable chart snapshots/revisions.

### Why this blocks closure

A process-local DTO is reproducible in tests but is not an auditable production chart history or release-quality read model.

### Remediation

Add a bounded chart schema owned by a dedicated chart domain contract:

- `chart_definitions` — chart identity, metric contract, eligibility, status;
- `chart_metric_observations` — immutable normalized provider observations;
- `chart_snapshots` — chart id, calculation version, snapshot time, input-set fingerprint;
- `chart_snapshot_rows` — rank, canonical recording id, score/ranking basis;
- snapshot-to-observation provenance relation or immutable observation-id payload.

Enforce uniqueness for observation fingerprints and snapshot revisions. Never mutate historical snapshots in place.

## Blocker 3 — No public chart route backed by persisted snapshots

### Evidence

`PublicChartProjection` resolves chart rows to canonical recording title/slug/URL, but no production chart route currently owns a persisted snapshot read path. The product roadmap also reserves broader public chart UX for Stage 23.

### Why this blocks closure

The Stage 22.1 golden flow requires a public projection, while current Stage 23 authority owns broader chart UX. Stage 22 may establish the read contract, but should not silently absorb a full unrelated redesign.

### Remediation

After persisted snapshots exist, add the smallest non-redesign public surface:

- read-only `/charts/{chart}` route;
- latest immutable snapshot read model;
- canonical recording URLs only;
- calculation version + snapshot timestamp + source/provenance disclosure;
- HTTP/feature coverage for canonical identity consistency.

Leave visual redesign and richer chart browsing to Stage 23.

## Blocker 4 — Exact-head Auto Closure requires successful execution

### Evidence

Earlier PR heads reached GitHub Actions `action_required`/pending states without executable jobs. Any new commit invalidates prior verification evidence.

### Remediation

Approve/enable the pending SongChart Auto Closure workflow when GitHub requests repository-side approval, then require the exact current head to complete PREPARE, QUALITY, impacted database/browser/frontend lanes and canonical CLOSE successfully. If a job fails after it starts, use the job log as the next implementation owner rather than marking the stage accepted manually.

## Authority conflict note

`docs/project/docs/ROADMAP.md` still labels Stage 22 as Design System Authority, while the active `stage-plan.json` owns Stage 22.1 as production vertical closure. The roadmap itself states that `stage-plan.json` owns the active stage, so current execution follows the stage plan. Before Stage 22 is finally accepted, the roadmap naming should be reconciled so future sessions do not interpret Stage 22 differently.

## Closure rule

Stage 22 must remain blocked until blockers 1–3 are implemented with real production evidence and blocker 4 passes on the exact head. Tests using deterministic fixtures may prove algorithm and invariants, but they must not be used to claim a real SongChart chart exists when no real metric source has emitted the inputs.

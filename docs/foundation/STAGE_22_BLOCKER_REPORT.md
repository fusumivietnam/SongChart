# Stage 22 — Blocker Report

Status: active blocker authority for Stage 22.1 closure.

## Implemented and closed

- Critical intersection audit contains no unknown production edge.
- Golden provider/canonical cases cover single-provider replay, multi-provider agreement, provider conflict, ambiguous identity and remaster/version semantics.
- Canonical admission emits `CanonicalEntityChanged` after governed mutation.
- Active Discovery channels for the changed entity type are queued for bounded reprojection.
- Chart inputs require canonical recording identity plus provider/item/timestamp provenance.
- Chart calculation is deterministic and calculation-versioned, with a stable input fingerprint and retained observation set.
- `chart_snapshots` provides idempotent persisted snapshot ownership; identical version/input replay resolves to one stored snapshot.
- `PublicChartProjection` resolves rows back to canonical Recording identity.
- Read-only `/charts/{chart}` publishes only an existing persisted snapshot and returns 404 when no snapshot exists. No chart data is fabricated.

## Remaining production blocker — No real provider metric source

### Evidence

Current provider integrations cover catalog/enrichment/destination behavior, but the repository still has no production time-series metric source that can lawfully and stably emit comparable observations for SongChart chart calculation. Product/UI authority forbids fabricated chart, listener or popularity values.

### Why this blocks Stage 22 acceptance

The chart calculator, persistence and public read path are now implemented, but deterministic test observations are fixtures, not production evidence. The golden vertical requires a real traceable provider observation to reach `ChartMetricObservation`, persist a snapshot, and appear on the public chart.

### Remediation — Stage 22.2 provider metric ingestion

Adopt one provider only after its official API/data terms support a stable metric suitable for SongChart charting. The adapter must emit these minimum fields:

- provider slug;
- provider item identity;
- canonical recording identity after governed resolution;
- metric key and explicit unit/meaning;
- observed-at/source timestamp;
- fetched-at timestamp;
- numeric value;
- immutable observation id/fingerprint;
- raw/source provenance pointer.

Then add a bounded application pipeline:

1. fetch real provider metric observation;
2. normalize to the provider-neutral chart observation contract;
3. resolve/require canonical Recording identity;
4. select affected chart definition/metric contract;
5. build a versioned snapshot;
6. append it idempotently to `chart_snapshots`;
7. expose the latest persisted result through the existing public chart route;
8. on relevant canonical/provider observation changes, enqueue only affected chart recomputation.

Do not sum values across providers unless the metric contract explicitly declares the values comparable. The current calculator is valid for one explicit comparable metric; provider normalization owns unit/meaning compatibility.

## Verification blocker — Exact-head Auto Closure

Earlier PR heads reached GitHub Actions `action_required` or remained pending without executable jobs. Any new commit invalidates previous verification evidence.

### Remediation

Approve/enable the pending `SongChart Auto Closure` workflow if GitHub requests repository-side approval. The exact current head must then complete PREPARE, QUALITY, impacted PostgreSQL/browser/frontend lanes and canonical CLOSE. If jobs start and fail, their logs become the next concrete implementation owner; Stage 22 must not be marked accepted manually.

## Authority conflict to reconcile

`docs/project/docs/ROADMAP.md` labels Stage 22 as Design System Authority while active `stage-plan.json` owns Stage 22.1 as production vertical closure. The roadmap itself delegates the active stage to `stage-plan.json`, so implementation follows the stage plan. Before final Stage 22 acceptance, reconcile the roadmap label so future sessions do not interpret Stage 22 differently.

## Closure rule

Stage 22 remains blocked until one real production metric source completes the provider → canonical → chart snapshot → public route vertical and exact-head Auto Closure passes. Fixture tests prove contracts and failure modes; they do not constitute real chart data.

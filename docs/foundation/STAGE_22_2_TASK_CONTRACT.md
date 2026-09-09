# Stage 22.2 — Data Spine Closure

## Status

Verification pending for major-stage closure.

All bounded tranches `22.2A` through `22.2D` are implemented on the active Stage 22 branch. Stage acceptance still requires exact-current-head Auto Closure.

## Purpose

Close the deterministic SongChart data spine before expanding AI, MCP, control-plane automation or role-specific intelligence. Stage 22.2 turns provider/catalog/chart data from adjacent capabilities into one traceable, freshness-aware, correction-safe data path.

The target spine is:

```text
provider evidence
  → normalized observation
  → identity/canonical resolution
  → metric observation history
  → chart definition/input selection
  → chart snapshot
  → public/search/discovery projection
  → governed correction
  → bounded downstream recomputation
```

## Product outcome

For a canonical Recording, SongChart can deterministically answer:

- which provider destinations/evidence are bound to it;
- which metric observations exist and when they were observed/fetched;
- which chart definitions consume those observations;
- which latest chart snapshot contains the Recording;
- whether observation/snapshot state is fresh, stale or unknown;
- which active chart definitions must be recomputed after a governed correction.

No external AI model or MCP runtime is required.

## Invariants

1. Canonical identity remains provider-neutral.
2. Provider evidence, persisted metric observations and derived snapshots remain distinguishable and traceable.
3. Metric history preserves provider, unit, semantics version, observed time, fetched time and source reference.
4. Observation identity is idempotent; an id reused for different evidence fails closed.
5. Metrics with incompatible semantics are never silently mixed.
6. Chart definitions own chart id, input metric, semantic boundary, aggregation, dependency and freshness policy; snapshots remain immutable historical results.
7. Freshness is explicit; unknown freshness is not treated as fresh.
8. A canonical correction selects bounded downstream chart consumers through declared dependencies rather than global rebuilds.
9. Public surfaces never fabricate metric/chart data when source evidence is absent or invalid.
10. AI-ready/MCP-ready interfaces remain consumers of application/domain capabilities.

## 22.2A — Unified observation and metric time-series contract

Status: implemented; verification pending.

Implemented:
- append-only PostgreSQL `chart_metric_observations` history;
- deterministic observation-id uniqueness and idempotent replay;
- fail-closed observation-id collision handling;
- provider/canonical/provider-item/metric/unit/semantics/value/observed/fetched/source persistence;
- `DatabaseChartMetricObservationStore` rehydration contract;
- YouTube refresh now persists and reloads metric observations before chart calculation;
- release-authoritative schema ownership and persistence tests.

## 22.2B — Chart definition and dependency authority

Status: implemented; verification pending.

Implemented:
- `docs/project/domain/chart-definitions.json` as authored chart-definition authority;
- explicit YouTube metric/unit/semantics/calculation/aggregation/freshness/dependency policy;
- `ChartDefinitionRegistry` deterministic loader/validator;
- `PlanChartRecompute` dependency-driven canonical-change selection;
- provider-specific refresh execution remains outside canonical models.

## 22.2C — Freshness and lineage / data trace

Status: implemented; verification pending.

Implemented:
- source-based observation and snapshot freshness states (`fresh`, `stale`, `unknown`);
- `RecordingDataTrace` read-only application query;
- provider destination → metric observation → chart definition → snapshot → public URL lineage;
- `songchart:data:trace {recording} --json` machine-readable CLI surface;
- no model/API/MCP dependency.

## 22.2D — Generalized correction propagation

Status: implemented; verification pending.

Implemented:
- canonical chart recomputation selection is driven by chart-definition dependencies;
- Recording changes select only active chart definitions that declare Recording dependency;
- current YouTube execution remains bounded and opt-in by provider runtime state;
- non-Recording changes do not trigger chart refresh;
- existing direct search reads and Discovery reprojection ownership remain unchanged.

## Authority and official sources

- `docs/project/engineering/system-intersection-map.json`
- `docs/project/engineering/golden-flow-contract.json`
- `docs/project/domain/chart-definitions.json`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/project/domain/application-data-boundary.json`
- `docs/providers/PROVIDER_TAXONOMY.md`
- `docs/chart/YOUTUBE_VIEW_COUNT_CONTRACT.md`
- `docs/project/stack/impact-test-map.json`
- `docs/project/engineering/verification-consumer-graph.json`

## Tests and verification

Focused evidence is owned by:

- `tests/Feature/Chart/ChartMetricObservationPersistenceTest.php`
- `tests/Feature/Chart/DataSpineContractTest.php`
- `tests/Feature/Chart/YouTubeViewChartFlowTest.php`
- existing chart provenance/persistence/public-projection tests;
- existing canonical-change reprojection tests.

Major-stage closure remains:

```bash
./songchart impact --verify
./songchart candidate
./songchart verify
```

Auto Closure on the exact current source/effective prepared head is required before Stage 22.2 becomes accepted.

## Explicit non-goals

- MCP server/runtime implementation.
- OpenAI/Anthropic/other paid model integration.
- Local LLM/Ollama runtime deployment.
- New provider adoption.
- Public Stage 23 redesign/personalization.
- Data warehouse/lakehouse or separate analytics platform.
- Microservices/Kubernetes expansion.
- Autonomous production writes/self-healing.

## Follow-on

After Stage 22.2 closes, Stage 22.3 may expose these deterministic capabilities through an AI-ready control plane. MCP remains a future thin adapter rather than a Stage 22.2 runtime requirement.

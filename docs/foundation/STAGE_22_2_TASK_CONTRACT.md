# Stage 22.2 — Data Spine Closure

## Status

Active.

Active tranche: `22.2A — Unified observation and metric time-series contract`.

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

- which provider evidence supports its identity;
- which metric observations exist and when they were observed/fetched;
- which chart definitions consume those observations;
- which snapshots/public projections depend on the entity;
- whether each projection is fresh, stale or unavailable;
- what must be recomputed after a governed correction.

No external AI model or MCP runtime is required for these capabilities.

## Invariants

1. Canonical identity remains provider-neutral.
2. Raw/provider evidence, normalized evidence and derived metrics remain distinguishable and traceable.
3. Metric history preserves provider, unit, semantics version, observed time, fetched time and source reference.
4. Metrics with incompatible semantics are never silently mixed.
5. Chart definitions own input selection/ranking semantics; snapshots remain immutable historical results.
6. Freshness is explicit; unknown freshness is not treated as fresh.
7. A canonical correction selects bounded downstream consumers through declared dependencies rather than global rebuilds.
8. Public surfaces never fabricate metric/chart data when source evidence is absent/stale/invalid.
9. AI-ready/MCP-ready interfaces consume application/domain capabilities; they do not become the business owner.
10. Stage implementation must reuse existing Laravel/Application/Domain owners before creating new abstractions.

## Tranches

### 22.2A — Unified observation and metric time-series contract

Status: active.

Goals:
- Inventory existing provider evidence and `ChartMetricObservation` semantics.
- Define the smallest shared observation/time-series contract needed by chart/data-lineage consumers.
- Persist metric observations/history without mutating historical evidence.
- Preserve provider, canonical Recording, provider item, metric, unit, semantics version, observed/fetched timestamps, source reference and deterministic identity/fingerprint.
- Bind the existing YouTube view-count source to the history contract without changing provider/canonical ownership.

Acceptance:
- Repeated identical observation persistence is idempotent.
- Historical observations are append-only/self-identifying.
- Incompatible metric semantics fail closed at chart-selection/calculation boundaries.
- PostgreSQL schema ownership and upgrade safety are explicit.
- Existing YouTube chart flow reads through the governed observation contract rather than a parallel data path.

### 22.2B — Chart definition and dependency authority

Status: planned.

Goals:
- Introduce a deterministic chart-definition owner for chart id, entity type, input metric, semantic constraints, selection policy and calculation version.
- Make entity/chart dependencies queryable without embedding vendor-specific logic in canonical models.
- Replace chart-specific recompute selection with dependency-driven bounded selection where justified.

Acceptance:
- A chart snapshot can be traced to one versioned chart definition and input observation set.
- A canonical Recording change can determine which active chart definitions are affected.
- Provider-specific details remain adapter concerns.

### 22.2C — Freshness and lineage / data-trace surfaces

Status: planned.

Goals:
- Define freshness state for provider observations, metric history, chart snapshots and projections.
- Expose a read-only application service/CLI JSON surface that traces a canonical entity through evidence, metrics, charts and public projections.
- Keep the surface deterministic and usable by humans, scripts and future AI/MCP adapters.

Acceptance:
- `fresh`, `stale`, `unavailable/unknown` semantics are machine-readable and source-based.
- One Recording can be traced end-to-end without direct ad-hoc database inspection.
- No external model/API call is required.

### 22.2D — Generalized correction propagation

Status: planned.

Goals:
- Generalize canonical-change downstream propagation from hard-coded chart-specific behavior to declared bounded consumers where the data spine proves the dependency.
- Preserve existing Discovery/search behavior when direct canonical reads already provide convergence.
- Avoid broad invalidation/rebuild when a narrower dependency owner exists.

Acceptance:
- Governed correction produces deterministic downstream recomputation/invalidation decisions.
- Tests prove unaffected projections are not unnecessarily rebuilt.
- Data lineage records enough evidence to diagnose convergence failures.

## Existing authorities reused

- `docs/project/engineering/system-intersection-map.json`
- `docs/project/engineering/golden-flow-contract.json`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/project/domain/application-data-boundary.json`
- `docs/providers/PROVIDER_TAXONOMY.md`
- `docs/chart/YOUTUBE_VIEW_COUNT_CONTRACT.md`
- `docs/project/stack/impact-test-map.json`
- `docs/project/engineering/verification-consumer-graph.json`

## Verification

Use the existing governed topology. During implementation use impact-selected focused verification; stage closure remains:

```bash
./songchart impact --verify
./songchart candidate
./songchart verify
```

Database-backed changes require release-authoritative PostgreSQL evidence and forward migration safety.

## Explicit non-goals

- MCP server/runtime implementation.
- OpenAI/Anthropic/other paid model integration.
- Local LLM/Ollama runtime deployment.
- New provider adoption unless an existing-source limitation blocks the bounded data-spine contract.
- Public Stage 23 redesign/personalization.
- Data warehouse/lakehouse or separate analytics platform.
- Microservices/Kubernetes expansion.
- Autonomous production writes/self-healing.

## Follow-on

After Stage 22.2 closes, the same deterministic capabilities can be exposed through an AI-ready control plane and later a thin MCP adapter. SongChart must remain fully operable when no AI model is available.

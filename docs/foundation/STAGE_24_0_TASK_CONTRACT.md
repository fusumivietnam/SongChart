# Stage 24.0 — Operational Intelligence

## Status

Implementing. Stage 24 is activated from accepted `main` SHA `18cf355aff62afca35fe600483dc14ad02c4a0c3` after Stage 23.1.2 PR #35 was human-authorized/agent-merged and accepted-main verification run `34921198032` passed quality, PostgreSQL 18, frontend build, browser smoke and exact-head classification. The Stage 24 work lease is `stage-24-operational-intelligence`.

Active tranche: `24.0A` — Runtime telemetry baseline.

## Goal

Turn existing Laravel Pulse, Horizon, application/runtime diagnostics and provider health evidence into one bounded operational-intelligence layer that answers when SongChart is healthy, degraded, saturated or ready to scale. Stage 24 must prefer existing repository/runtime authorities and measurable thresholds over speculative infrastructure adoption.

## Invariants

1. Pulse and Horizon remain the existing first-party runtime/queue observability owners; Stage 24 must not create a parallel monitoring stack for data they already own.
2. Operational intelligence is evidence and decision support, not product/domain truth.
3. Metrics must distinguish unavailable/unknown evidence from valid zero values.
4. Provider failures, freshness and ingestion health remain provider/evidence concerns and never become canonical identity authority.
5. Runtime collection must not add request-time source scans or expensive synchronous diagnostics.
6. No external observability service becomes a prerequisite for local development, canonical verification or product correctness.
7. External APM/Grafana/MCP adoption requires a repository-recorded gap, metric/threshold and expected improvement.
8. No automatic scaling, infrastructure mutation or production remediation is introduced without a separate human-gated authority.
9. Existing security/redaction boundaries apply to telemetry; credentials, raw secrets and sensitive connection material must never be exported.
10. Stage closure reuses the existing SongChart verification topology and exact-head Auto Closure; no second CI/verification authority is introduced.

## Tranches

### 24.0A — Runtime telemetry baseline

- inventory existing Pulse, Horizon, health/runtime and verification evidence owners;
- define a single operational metric vocabulary for latency, saturation, queue wait/pressure, database connections/query latency and cache behavior;
- expose bounded read-only operational status through existing Project Kernel/Admin/CLI surfaces only where an owner already exists;
- preserve unknown/unavailable semantics and evidence timestamps.

### 24.0B — Provider and data-pipeline health

- converge provider failures, ingestion/import/quarantine health and freshness lag into the operational read model;
- retain provider-specific evidence while presenting provider-neutral system health;
- define alert-worthy failure classes without duplicating provider workflow ownership.

### 24.0C — Scale scorecard and decision thresholds

- compose runtime, DB/cache, queue, provider and search-visibility evidence into a scorecard;
- define measurable thresholds and decision rules for cache, queue, database and delivery scaling investigations;
- record insufficient evidence explicitly instead of recommending speculative infrastructure.

### 24.0D — External observability evaluation and stage closure

- evaluate Grafana Cloud/MCP or equivalent only against demonstrated internal observability gaps;
- adopt no external dependency unless value, operational cost, data exposure and fallback behavior are justified;
- close Stage 24 with exact-head PostgreSQL, browser, frontend, classification, canonical CLOSE and exact-tree evidence.

## Acceptance criteria

Stage 24 acceptance requires one repository-owned operational metric vocabulary; bounded read-only telemetry/status composition; explicit unknown/freshness semantics; Pulse/Horizon reuse; provider/data-pipeline health integration; a measurable scale scorecard with decision thresholds; no speculative external dependency; security/redaction preservation; tests for metric/status classification; and normal exact-head Auto Closure on every accepted tranche and the final stage head.

## Authority and official sources

Repository authorities include `PROJECT_AUTHORITY.md`, `docs/project/engineering/stage-plan.json`, `docs/project/docs/ROADMAP.md`, existing Pulse/Horizon/runtime/provider-health contracts, `docs/project/engineering/verification-topology.json`, `docs/project/engineering/verification-command-surface.json`, `docs/project/stack/impact-test-map.json` and `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`.

Installed package/runtime versions remain lockfile-owned. Stage 24 starts by reusing installed Laravel/Pulse/Horizon capabilities and existing SongChart diagnostics; no new package is authorized by activation alone.

## Activation evidence

1. Stage 23.0 is accepted in repository authority.
2. Post-Stage-23 optimization PR #35 (`Stage 23.1.2 — Workflow evidence reuse + diagnostics`) passed Auto Closure run `34920816712` on exact head `c2da403d97527354bcfc05493fb95b8214f859e4` and was merged.
3. Accepted `main` SHA is `18cf355aff62afca35fe600483dc14ad02c4a0c3`.
4. Accepted-main workflow run `34921198032` passed quality, PostgreSQL 18, frontend build, browser smoke and exact-head classification.
5. No existing open Stage 24 PR/work lease existed when activation began.

## Verification ownership

Repository-owned verification remains:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Stage 24 may extend existing verification consumers when needed, but must not add a second verification framework.

## Handoff

Stage 24 begins with `24.0A`. Repository and live GitHub state remain authoritative over chat memory. One umbrella Stage 24 branch owns overlapping Stage 24 source until a synchronization/acceptance point.
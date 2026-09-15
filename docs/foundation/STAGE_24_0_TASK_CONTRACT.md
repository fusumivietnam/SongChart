# Stage 24.0 — Operational Intelligence

## Status

Implementing. Stage 24 is activated from accepted `main` SHA `18cf355aff62afca35fe600483dc14ad02c4a0c3` after Stage 23.1.2 PR #35 was merged and accepted-main verification run `34921198032` passed quality, PostgreSQL 18, frontend build, browser smoke and exact-head classification. The Stage 24 work lease is `stage-24-operational-intelligence`.

Active tranche: `24.0A` — Runtime telemetry baseline.

## Goal

Turn existing Laravel Pulse, Horizon, application/runtime diagnostics and provider health evidence into one bounded operational-intelligence layer that answers when SongChart is healthy, degraded, saturated or ready to scale. Stage 24 prefers existing repository/runtime authorities and measurable thresholds over speculative infrastructure adoption.

## Invariants

1. Pulse and Horizon remain the first-party runtime/queue observability owners; Stage 24 must not create a parallel monitoring stack for data they already own.
2. Operational intelligence is evidence and decision support, not product/domain truth.
3. Metrics distinguish unavailable/unknown evidence from valid zero values.
4. Provider failures, freshness and ingestion health remain provider/evidence concerns and never become canonical identity authority.
5. Runtime collection adds no request-time source scans or expensive synchronous diagnostics.
6. No external observability service becomes a prerequisite for local development, canonical verification or product correctness.
7. External APM/Grafana/MCP adoption requires a repository-recorded gap, metric/threshold and expected improvement.
8. No automatic scaling, infrastructure mutation or production remediation is introduced without separate human-gated authority.
9. Existing security/redaction boundaries apply to telemetry; credentials, raw secrets and sensitive connection material must never be exported.
10. Stage closure reuses the existing SongChart verification topology and exact-head Auto Closure; no second CI/verification authority is introduced.

## Tranches

### 24.0A — Runtime telemetry baseline

- inventory existing Pulse, Horizon, health/runtime and verification evidence owners;
- define one operational metric vocabulary for latency, saturation, queue wait/pressure, database connections/query latency and cache behavior;
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

Stage 24 acceptance requires one repository-owned operational metric vocabulary; bounded read-only telemetry/status composition; explicit unknown/freshness semantics; Pulse/Horizon reuse; provider/data-pipeline health integration; a measurable scale scorecard with decision thresholds; no speculative external dependency; security/redaction preservation; tests for metric/status classification; and normal exact-head Auto Closure on the final stage head.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/domain/operational-contracts.json`
- `docs/operations/pulse-observability.md`
- `docs/operations/queue-infrastructure.md`
- existing provider health/import/quarantine contracts and read models
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`

### Installed versions

Exact package/runtime versions remain lockfile-owned. The activation baseline uses the repository-locked PHP 8.5, Laravel 13, PostgreSQL 18, Laravel Horizon 5.x and Laravel Pulse 1.x. Stage 24 does not authorize a package upgrade or new external observability dependency merely by activation.

### Official external sources

Official Laravel documentation is the authority for Laravel Pulse and Horizon capability/behavior. PostgreSQL official documentation is the external authority for database/runtime metrics where PostgreSQL semantics are used. External observability vendor documentation may inform the 24.0D evaluation but cannot override SongChart repository-owned thresholds, privacy, security, product or verification authority.

### Native capability assessment

The accepted baseline already contains Laravel Pulse for application observability, Horizon for Redis queue supervision, provider health/sync evidence, import/quarantine state, database/runtime diagnostics and repository-owned Admin/CLI read surfaces. Stage 24 therefore starts reuse-first: compose these existing authorities before considering custom collectors or third-party APM infrastructure.

### Custom implementation justification

SongChart-specific composition is justified only for the bounded cross-owner read model and scale-decision semantics that first-party packages cannot know: provider/data freshness, import/quarantine failure classes, SongChart-specific unknown/stale semantics, evidence timestamps and repository-approved scaling thresholds. Custom code must remain read-only and thin; collection/supervision stays with its existing owner whenever possible.

## Activation evidence

1. Stage 23.0 is accepted in repository authority.
2. Post-Stage-23 optimization PR #35 passed Auto Closure run `34920816712` on exact head `c2da403d97527354bcfc05493fb95b8214f859e4` and was merged.
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

## Tests and verification

Each implementation change must pass the repository-selected focused/impact tests and quality verification. Stage closure requires PostgreSQL 18, production frontend build, desktop/mobile browser smoke where affected, exact-head failure classification, canonical CLOSE, exact-tree preservation and ready-to-promote on the exact PR head. Generated repository authority remains PREPARE-owned rather than hand-edited.

## Handoff

Stage 24 begins with `24.0A`. Repository and live GitHub state remain authoritative over chat memory. One umbrella Stage 24 branch owns overlapping Stage 24 source until a synchronization/acceptance point.
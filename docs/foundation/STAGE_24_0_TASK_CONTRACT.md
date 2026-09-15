# Stage 24.0 — Operational Intelligence

## Status

Accepted. Stage 24 was activated from accepted `main` SHA `18cf355aff62afca35fe600483dc14ad02c4a0c3` after Stage 23.1.2 PR #35 was merged and accepted-main verification run `34921198032` passed. The Stage 24 work lease is `stage-24-operational-intelligence`.

Accepted implementation head: `a7bc052933d4e2f86403db7317ab7a2a392360bc`, Auto Closure run `34924143552` (#518). PREPARE/QUALITY, PostgreSQL 18, production frontend build, desktop/mobile browser smoke, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote all passed.

Accepted tranches:

- `24.0A` — Runtime telemetry baseline.
- `24.0B` — Provider and data-pipeline health.
- `24.0C` — Scale scorecard and decision thresholds.
- `24.0D` — External observability evaluation and stage closure.

No Stage 24 implementation tranche remains active. Stage 25 requires separate repository-authored activation authority after Stage 24 is merged and accepted-main verification passes.

## Goal

Turn existing Laravel Pulse, Horizon, application/runtime diagnostics and provider health evidence into one bounded operational-intelligence layer that answers when SongChart is healthy, degraded, saturated or ready to scale. Stage 24 prefers existing repository/runtime authorities and measurable thresholds over speculative infrastructure adoption.

## Invariants

1. Pulse and Horizon remain the first-party runtime/queue observability owners; Stage 24 does not create a parallel monitoring stack for data they already own.
2. Operational intelligence is evidence and decision support, not product/domain truth.
3. Metrics distinguish unavailable/unknown evidence from valid zero values.
4. Provider failures, freshness and ingestion health remain provider/evidence concerns and never become canonical identity authority.
5. Runtime collection adds no request-time source scans or expensive synchronous diagnostics.
6. No external observability service is a prerequisite for local development, canonical verification or product correctness.
7. External APM/Grafana/MCP adoption requires a repository-recorded gap, metric/threshold and expected improvement.
8. No automatic scaling, infrastructure mutation or production remediation is introduced without separate human-gated authority.
9. Existing security/redaction boundaries apply to telemetry; credentials, raw secrets and sensitive connection material are never exported.
10. Stage closure reuses the existing SongChart verification topology and exact-head Auto Closure; no second CI/verification authority is introduced.

## Accepted tranches

### 24.0A — Runtime telemetry baseline

Accepted outcomes:

- existing Pulse, Horizon, health/runtime and verification evidence owners are reused rather than duplicated;
- one operational metric vocabulary covers latency, saturation, queue pressure, database/query behavior and cache behavior;
- bounded read-only operational status is exposed through the existing CLI/read-model boundary;
- unknown/unavailable evidence remains distinct from observed zero.

### 24.0B — Provider and data-pipeline health

Accepted outcomes:

- provider failures, sync freshness and ingestion/import/quarantine evidence are composed into the operational read model;
- provider-specific evidence remains evidence rather than canonical identity authority;
- data-pipeline failure classes remain bounded and read-only.

### 24.0C — Scale scorecard and decision thresholds

Accepted outcomes:

- runtime, queue, database, cache and provider evidence feed one scale scorecard;
- scaling investigation uses repository-owned thresholds rather than speculative infrastructure recommendations;
- missing required dimensions produce `insufficient_evidence` rather than a fabricated healthy or scale-ready result;
- automatic infrastructure mutation remains disabled.

### 24.0D — External observability evaluation and stage closure

Accepted outcomes:

- external APM/Grafana remains deferred because no demonstrated internal observability gap justifies a new production dependency;
- future adoption requires measurable value, security/privacy review, operational cost and fallback behavior;
- Stage 24 passed exact-head PostgreSQL, browser, frontend, classification, canonical CLOSE and exact-tree verification.

## Acceptance criteria

Stage 24 acceptance requires one repository-owned operational metric vocabulary; bounded read-only telemetry/status composition; explicit unknown/freshness semantics; Pulse/Horizon reuse; provider/data-pipeline health integration; a measurable scale scorecard with decision thresholds; no speculative external dependency; security/redaction preservation; tests for metric/status classification; and normal exact-head Auto Closure on the final stage head.

These criteria are satisfied by the accepted implementation and run `34924143552`; the final authored closure head must still pass the same exact-head Auto Closure before merge.

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

Exact package/runtime versions remain lockfile-owned. The accepted implementation uses the repository-locked PHP 8.5, Laravel 13, PostgreSQL 18, Laravel Horizon 5.x and Laravel Pulse 1.x. Stage 24 adds no external observability dependency.

### Official external sources

Official Laravel documentation is the authority for Laravel Pulse and Horizon capability/behavior. PostgreSQL official documentation is the external authority for database/runtime metrics where PostgreSQL semantics are used. External observability vendor documentation may inform future evaluation but cannot override SongChart repository-owned thresholds, privacy, security, product or verification authority.

### Native capability assessment

The accepted baseline already contains Laravel Pulse for application observability, Horizon for Redis queue supervision, provider health/sync evidence, import/quarantine state, database/runtime diagnostics and repository-owned Admin/CLI read surfaces. Stage 24 therefore composes these existing authorities before considering custom collectors or third-party APM infrastructure.

### Custom implementation justification

SongChart-specific composition is limited to the bounded cross-owner read model and scale-decision semantics that first-party packages cannot know: provider/data freshness, import/quarantine failure classes, SongChart-specific unknown/stale semantics, evidence timestamps and repository-approved scaling thresholds. Custom code remains read-only and thin; collection/supervision stays with its existing owner whenever possible.

## Activation evidence

1. Stage 23.0 is accepted in repository authority.
2. Post-Stage-23 optimization PR #35 passed Auto Closure run `34920816712` on exact head `c2da403d97527354bcfc05493fb95b8214f859e4` and was merged.
3. Accepted activation `main` SHA is `18cf355aff62afca35fe600483dc14ad02c4a0c3`.
4. Accepted-main workflow run `34921198032` passed quality, PostgreSQL 18, frontend build, browser smoke and exact-head classification.
5. Stage 24 implementation exact head `a7bc052933d4e2f86403db7317ab7a2a392360bc` passed Auto Closure run `34924143552` (#518), including canonical CLOSE, exact-tree preservation and ready-to-promote.

## Verification ownership

Repository-owned verification remains:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Stage 24 does not introduce a second verification framework.

## Tests and verification

Stage 24 passed repository-owned quality verification, PostgreSQL 18 tests, production frontend build, desktop/mobile browser smoke, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote on implementation head `a7bc052933d4e2f86403db7317ab7a2a392360bc` in Auto Closure run `34924143552`.

The closure-authority commit is not merge evidence by itself; it must pass a fresh exact-head Auto Closure before PR #36 may be merged.

## Handoff

Stage 24 is accepted in authored authority only after `docs/project/engineering/stage-plan.json` records the matching accepted state. Repository and live GitHub state remain authoritative over chat memory. Stage 25 — Global Delivery & Scale is roadmap-next but requires a separate activation after Stage 24 merge and accepted-main verification.
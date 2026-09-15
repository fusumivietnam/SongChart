# Stage 27.0 — Product Signals & Retention

## Status

Bounded post-Stage-26 task contract. Stage 26 remains the accepted baseline on `main`. This contract authorizes only the first tranche, `27.0A — Minimal Product Event Contract`, until repository stage authority is activated and exact-head verification passes.

## Goal

Create the minimum trustworthy product-signal foundation required to measure real user demand and retention before building retention loops, demand intelligence, recommendations, community features, visitor AI, or adaptive automation.

Stage 27 must keep product telemetry separate from canonical music-domain authority and from privileged/business audit history.

## Ponytail implementation posture

Stop at the first solution that fully holds:

1. audit and reuse existing Laravel/application events, request context, PostgreSQL capability, existing audit infrastructure boundaries and verification ownership;
2. do not repurpose privileged audit logs as product analytics;
3. prefer typed application-owned event contracts and PostgreSQL-native durable storage before external analytics infrastructure;
4. add no event-streaming platform, warehouse, queue topology, analytics SDK, new SaaS, or generic event bus without demonstrated pressure;
5. capture only events with a named product decision or metric consumer;
6. record the minimum payload needed for that decision; raw query/user data is not retained merely because it is available.

Minimality must not weaken privacy, abuse resistance, canonical authority, data integrity, accessibility, or verification.

## Invariants

1. Repository authority remains primary over chat/agent memory and external analytics tools.
2. Product telemetry is evidence about product usage; it is never canonical music-domain truth.
3. `spatie/laravel-activitylog` remains privileged/business audit storage/query infrastructure only. Product analytics must not silently expand its authority.
4. No telemetry event exists without an explicit producer, purpose, PII classification, retention rule, sampling rule and consumer/metric.
5. Public requests must not synchronously call external providers as a side effect of telemetry.
6. Anonymous identifiers, if required, must be bounded and privacy-classified; Stage 27 does not authorize cross-device fingerprinting.
7. Event payloads prefer stable SongChart identifiers and coarse enums over copied canonical records or arbitrary request blobs.
8. Derived metrics/snapshots are preferred over feeding raw event streams directly to AI.
9. Stage 27.0A is taxonomy/contract/storage/producer proof only. Favorites/collections closure, recent history, anonymous-to-account merge and retention optimization remain later tranches.
10. No recommendation graph, public playlists/community, visitor chatbot, experimentation platform, warehouse or streaming system is authorized by this contract.

## Tranches

### 27.0A — Minimal Product Event Contract

Only active implementation target once stage authority is activated.

Initial candidate event vocabulary, subject to repository audit before implementation:

- `search.performed`
- `search.zero_result`
- `entity.viewed`
- `relationship.clicked`
- `provider.clicked`
- `favorite.added`
- `favorite.removed`
- `collection.updated`

Do not implement a candidate event merely because it appears in this list. A producer and measurable consumer must exist or be part of the same bounded tranche.

Required closure for each implemented event:

- stable event name and schema/version;
- producer location and trigger semantics;
- actor/anonymous semantics;
- canonical entity reference semantics where relevant;
- purpose and owning metric;
- PII/privacy class;
- retention and deletion behavior;
- sampling/deduplication/idempotency semantics where relevant;
- consumer/query or derived metric proof;
- PostgreSQL-backed verification.

Minimum likely implementation shape:

- small application-owned event taxonomy/DTO or equivalent native Laravel contract;
- durable PostgreSQL event/evidence table only if current repository storage has no suitable non-audit owner;
- minimal recorder boundary callable from existing product flows;
- repository-owned verifier/tests that prevent privileged-audit/canonical-domain authority leakage;
- no external analytics dependency.

### 27.0B — Favorites & Collections Closure

Deferred until 27.0A is accepted. Audit current MVP behavior first; close only demonstrated persistence/product gaps and attach signal producers from the accepted event contract.

### 27.0C — Recent/Local State

Deferred. Evaluate recently viewed/recent searches and anonymous local state only where it improves the return loop without unnecessary server retention.

### 27.0D — Retention Measurement & Stage Closure

Deferred. Define bounded return/retention metrics from accepted signals; no recommendation or notification expansion without evidence.

## Explicit non-goals

- Google Analytics, Segment, Mixpanel, Amplitude or another analytics SaaS by default.
- Kafka, RabbitMQ, event streaming, warehouse or ETL platform.
- Reusing privileged audit as product analytics.
- Copying raw HTTP requests, headers, IP addresses, user agents or arbitrary search text into durable telemetry without an explicit privacy/product need.
- Recommendation/community/native-app/visitor-AI work.
- Autonomous product mutation based on telemetry.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- current search/entity/chart/account/favorites/collections implementation and tests
- privileged-audit authority and `docs/operations/privileged-audit.md`

### Native capability assessment

Before adding storage or abstractions, inspect existing Laravel events/listeners, application services, request/session/auth context, PostgreSQL tables/indexes, Spatie Activitylog boundaries, queues, cache and repository verification consumers. Prefer existing Laravel/PHP/PostgreSQL capability when it satisfies the contract.

### External systems

No external analytics system is required for 27.0A. Any later external analytics evaluation requires demonstrated query/scale/operations pressure, explicit privacy/retention/cost ownership and an exit path.

## Verification

Use existing SongChart ownership rather than a second telemetry test framework:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Focused tests must prove event schema/recording/privacy boundaries and PostgreSQL behavior. Exact-head Auto Closure remains the acceptance gate.

## Handoff rule

Audit first. Activate and implement only `27.0A`. Reuse existing capabilities where they fit; if current audit already provides a valid non-audit event owner, extend it instead of creating another subsystem. Do not begin 27.0B–27.0D until 27.0A has accepted evidence.
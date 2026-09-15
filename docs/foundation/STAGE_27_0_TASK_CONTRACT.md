# Stage 27.0 — Product Signals & Retention

## Status

Bounded post-Stage-26 task contract. Stage 26 is the accepted baseline. This contract authorizes only the first tranche, `27.0A — Minimal Product Event Contract`, until exact-head verification accepts it.

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

Active implementation target.

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
- PostgreSQL-backed verification when persistence is activated.

Current contract decision:

- `search.performed` and `search.zero_result` are approved at contract level because the search flow exposes a stable application boundary and measurable demand/zero-result metrics;
- `entity.viewed` remains candidate-only until a concrete metric/action consumer is demonstrated;
- relationship/provider click and favorites/collections events remain deferred until corresponding producer/consumer ownership exists;
- contract approval alone does not authorize persistence.

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
- `docs/project/product/product-event-contract.json`
- current search/entity/chart/account implementation and tests
- privileged-audit authority and `docs/operations/privileged-audit.md`

### Installed versions

Use repository lockfiles and `composer.json` as executable version authority. Stage 27 assumes the accepted Laravel 13 / PHP 8.5 / PostgreSQL 18 baseline and adds no new package by default.

### Official external sources

No external analytics provider is required for 27.0A. If a framework/package behavior question becomes material, consult the version-matched official Laravel/PHP/PostgreSQL documentation before custom implementation. No third-party analytics documentation is authoritative for SongChart product-event semantics.

### Native capability assessment

Before adding storage or abstractions, inspect existing Laravel events/listeners, application services, request/session/auth context, PostgreSQL tables/indexes, queues, cache and repository verification consumers. Prefer existing Laravel/PHP/PostgreSQL capability when it satisfies the contract. `spatie/laravel-activitylog` is explicitly excluded as a product-telemetry owner because its accepted authority is privileged/business audit.

### Custom implementation justification

Custom telemetry code is allowed only when the repository lacks a suitable non-audit owner and the implementation is smaller than adopting an external analytics/event platform. Any custom recorder/storage must be typed, bounded, disable-able, privacy-classified, PostgreSQL-verifiable and attached to a named metric consumer. No generic event bus is justified by 27.0A.

## Tests and verification

Use existing SongChart ownership rather than a second telemetry test framework:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Focused architecture tests must prove contract/privacy/authority boundaries. If persistence is later activated inside 27.0A, focused PostgreSQL tests must additionally prove schema, retention/deletion behavior and derived metric consumption. Exact-head Auto Closure remains the acceptance gate.

## Handoff rule

Implement only `27.0A`. Reuse existing capabilities where they fit. Do not begin 27.0B–27.0D until 27.0A has accepted evidence. Persistence remains deferred until every activation gate in `docs/project/product/product-event-contract.json` is satisfied.

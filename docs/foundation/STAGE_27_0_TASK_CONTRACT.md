# Stage 27.0 — Product Signals & Retention

## Status

Bounded post-Stage-26 task contract. Stage 26 is the accepted baseline. `27.0A — Minimal Product Event Contract` is accepted; this contract now authorizes only `27.0B — Favorites & Collections Closure` until exact-head verification accepts it.

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
9. Stage 27.0A accepted aggregate-only search signals. Stage 27.0B may add only the minimum authenticated favorites/user-collection behavior justified by current MVP scope and must not reuse canonical catalog collections as user-owned state.
10. No recommendation graph, public playlists/community, visitor chatbot, experimentation platform, warehouse or streaming system is authorized by this contract.

## Tranches

### 27.0A — Minimal Product Event Contract

Accepted.

Accepted implementation:

- `search.performed` and `search.zero_result` are the only active product signals;
- persistence is aggregate-only and first-page-only in `product_search_daily_aggregates`;
- grouping is limited to day + entity type filter + sort order;
- no raw event rows, query text/hash, user id, session id, IP, user agent, cookie value or request payload are stored;
- pagination, empty searches and disabled telemetry are excluded;
- PostgreSQL native `INSERT ... ON CONFLICT` performs atomic increments;
- telemetry write failure must not fail public search;
- `entity.viewed`, relationship/provider clicks and favorites/collections signals remain deferred until their producer/consumer ownership is demonstrated.

Acceptance evidence is owned by `docs/project/engineering/stage-plan.json` and Auto Closure run 657.

### 27.0B — Favorites & Collections Closure

Active implementation target.

Audit current MVP behavior first; close only demonstrated persistence/product gaps and attach signal producers only when the accepted product-event contract has a real producer and metric consumer.

Current audit findings:

- MVP scope explicitly includes favorites and user collections;
- the existing `collections` table/model belongs to the canonical catalog and has no user ownership; it must not be repurposed as user-library state;
- public `/collections` routes/catalog surfaces therefore remain canonical catalog behavior, not authenticated user collections;
- no current repository implementation was found for authenticated favorites/user collections.

Required 27.0B closure:

- define a distinct user-state owner that does not weaken canonical catalog authority;
- prefer one minimal saved-item/favorite primitive before introducing arbitrary playlist/community semantics;
- reuse existing authenticated account/application boundaries;
- authorize only stable canonical SongChart entity references that current product surfaces can resolve safely;
- define duplicate/idempotent add/remove semantics and user ownership enforcement;
- define deletion behavior when the user/account is removed and safe behavior when a referenced canonical entity becomes unavailable;
- expose only the minimum account/public interaction needed for MVP value;
- add `favorite.added` / `favorite.removed` signals only if the producer and a measurable retention/product consumer are implemented in the same tranche;
- do not create recommendation ranking, sharing, follows, comments, collaborative playlists or public community scope.

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
- Reusing canonical catalog `collections` as authenticated user-library persistence.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/SCOPE.md`
- `docs/project/docs/ARCHITECTURE.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/product/product-event-contract.json`
- current search/entity/chart/account implementation and tests
- canonical catalog collection model/schema/routes
- privileged-audit authority and `docs/operations/privileged-audit.md`

### Installed versions

Use repository lockfiles and `composer.json` as executable version authority. Stage 27 assumes the accepted Laravel 13 / PHP 8.5 / PostgreSQL 18 baseline and adds no new package by default.

### Official external sources

No external analytics provider is required for Stage 27. If a framework/package behavior question becomes material, consult the version-matched official Laravel/PHP/PostgreSQL documentation before custom implementation. No third-party analytics documentation is authoritative for SongChart product-event or user-library semantics.

### Native capability assessment

Before adding storage or abstractions, inspect existing Laravel authentication, policies, Eloquent relationships, application services, PostgreSQL tables/indexes, queues, cache and repository verification consumers. Prefer existing Laravel/PHP/PostgreSQL capability when it satisfies the contract. `spatie/laravel-activitylog` is explicitly excluded as a product-telemetry owner because its accepted authority is privileged/business audit.

### Custom implementation justification

Custom telemetry or user-state code is allowed only when the repository lacks a suitable owner and the implementation is smaller than adopting an external platform or generalized subsystem. Any custom recorder/storage must be typed, bounded, disable-able where appropriate, privacy-classified, PostgreSQL-verifiable and attached to a named product use case. No generic event bus, social graph or playlist platform is justified by Stage 27.

## Tests and verification

Use existing SongChart ownership rather than a second telemetry/user-state test framework:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Architecture tests must prove contract/privacy/authority boundaries. PostgreSQL tests must prove schema, user ownership, duplicate/idempotent semantics and deletion behavior for any 27.0B persistence. Existing browser/account coverage should be extended only when a user-visible interaction is introduced. Exact-head Auto Closure remains the acceptance gate.

## Handoff rule

Implement only `27.0B`. Reuse existing capabilities where they fit. Do not begin 27.0C–27.0D until 27.0B has accepted evidence. Do not broaden 27.0B into recommendations, community features, public playlists or canonical catalog collection redesign.
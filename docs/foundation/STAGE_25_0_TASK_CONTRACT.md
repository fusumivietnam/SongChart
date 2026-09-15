# Stage 25.0 — Global Delivery & Scale

## Status

Accepted. Stage 25 was activated from accepted `main` SHA `d5eb48fd36ae3796de55201adbbf75c6d8ee99f4` after Stage 24 PR #36 was merged and accepted-main workflow run `34925210011` passed quality, PostgreSQL 18, frontend build, desktop/mobile browser smoke and accepted-main provenance classification.

All declared tranches `25.0A` through `25.0D` are accepted. No next stage is activated by this contract; future roadmap direction remains intentionally unset until the planned post-Stage-25 survey is completed.

## Goal

Use the Stage 24 operational-intelligence scorecard to decide, with measurable evidence, when SongChart needs edge caching/CDN behavior, database connection/read scaling, regional delivery, load balancing, circuit breaking or external APM. Stage 25 preserves the first-release production topology as the supported fallback and does not promote an external platform merely because it is available.

## Invariants

1. Stage 24 operational intelligence is the evidence input for Stage 25 scaling decisions; missing evidence remains `insufficient_evidence` rather than a deployment recommendation.
2. Caddy remains the application TLS/static-edge authority unless an accepted external edge profile explicitly sits in front of it; external CDN/proxy adoption must not silently replace application/runtime authority.
3. PostgreSQL 18 remains durable structured-data authority. Hyperdrive, connection pooling, replicas or regional data services are optimization layers only and cannot change domain truth or write ownership.
4. Redis remains replaceable queue/cache/runtime state and is not promoted to durable authority.
5. Cache policy must separate public cacheable reads from authenticated, personalized, admin, mutation and provenance-sensitive responses.
6. Every cache/CDN rule requires invalidation/freshness semantics, bypass rules and a safe fallback path.
7. Workers/edge compute is optional and must not duplicate Laravel domain/business logic unless a separately accepted use case proves the boundary.
8. Read replicas may serve only explicitly classified read paths; writes and consistency-sensitive flows stay on the primary database boundary.
9. Load balancing, regional routing and circuit breaking require measurable availability/latency/traffic evidence and explicit failure semantics before adoption.
10. Envoy remains deferred unless multi-service, gRPC, mTLS, multi-region or traffic-control requirements actually exist.
11. External APM adoption must demonstrate a visibility gap not already satisfied by Pulse/Horizon/Stage 24 evidence and must include privacy, retention, cost and fallback review.
12. No automatic infrastructure mutation or production scaling action is introduced without a separate human-gated authority.
13. Direct DNS-to-Caddy and the accepted production Compose topology remain supported fallback paths.
14. Stage closure uses the existing exact-head Auto Closure and canonical verification ownership; no second CI or deployment authority is introduced.

## Tranches

### 25.0A — Edge delivery and cache policy baseline

Accepted on exact-head Auto Closure evidence recorded in `docs/project/engineering/stage-plan.json`.

- classify public routes/assets by cacheability, freshness, invalidation and bypass requirements;
- map Stage 24 latency/cache evidence to measurable thresholds for edge/CDN investigation;
- define a provider-neutral edge delivery profile that keeps direct Caddy delivery valid;
- evaluate Cloudflare-compatible CDN/proxy/Workers capabilities only where the profile identifies a concrete gap;
- prohibit authenticated/admin/mutation/private responses from accidental shared caching.

### 25.0B — Database connection and read scaling

Accepted on Auto Closure run `34971975078` (#564), with source head `425b70761449c16a6ff101d926760a52495d9f45` and effective prepared head `eec3b25b5a365ab3814c9cb87e248b233360bb26`.

- classify read paths eligible for connection pooling, Hyperdrive-equivalent acceleration or replicas;
- define consistency and failback rules that keep PostgreSQL primary authoritative;
- use connection saturation/query-latency evidence before promoting connection or replica infrastructure;
- preserve canonical writes and consistency-sensitive reads on the primary path;
- keep `pgsql` as the default connection and expose `pgsql_read` only as an explicit optional boundary whose unset configuration resolves to the primary endpoint;
- keep replica/read-endpoint activation disabled until accepted evidence exists; no transparent or automatic read routing is introduced in this tranche.

### 25.0C — Regional resilience and traffic control

Accepted on Auto Closure run `34972809650` (#568), source head `82901e8b3dd64753ff79358aac77e7af3f7d2b0c`.

- evaluate multi-instance delivery, load balancing, regional placement and circuit breaking against measured latency/availability/provider-failure evidence;
- define health, drain, retry and failover semantics before any routing automation;
- keep the current single-region/direct DNS-to-Caddy Compose topology as the default and failback profile until evidence justifies another topology;
- require health checks to be side-effect-free and distinguish application availability from dependency degradation;
- prohibit automatic cross-region database writes, hidden retry loops for mutations, and routing automation that can duplicate application/domain behavior;
- keep Envoy deferred unless repository evidence proves one of its explicit trigger requirements.

### 25.0D — External APM evaluation and stage closure

Accepted on Auto Closure run `34977446255` (#575), with source head `f950a736d396fcc60e86bc8776abee4462e78bdc` and effective prepared head `7c8f07167fe3e48345de7bbd47ca7d8cbc631afa`.

- external APM was evaluated only against demonstrated Stage 24/25 observability gaps;
- value, privacy/retention, operational cost and exit/fallback behavior are recorded in `docs/operations/external-observability-evaluation.md`;
- decision is `deferred_no_demonstrated_gap` while the current repository-owned evidence baseline remains sufficient;
- no external APM package, agent, sidecar, proxy, credential, network dependency or automatic infrastructure mutation is introduced;
- exact-head quality, PostgreSQL, frontend, browser, classification, canonical CLOSE and exact-tree evidence passed.

## Acceptance criteria

Stage 25 acceptance requires a repository-owned edge/cache policy with safe bypass/invalidation semantics; metric-backed thresholds for edge, database and regional scaling investigations; explicit primary/replica consistency rules; provider-neutral failure/fallback behavior; no speculative Workers/Hyperdrive/load-balancer/Envoy/APM dependency; security/privacy preservation; tests/verifiers for policy classification; and exact-head Auto Closure on the final stage head.

For 25.0B specifically, acceptance requires explicit read-path classification in `docs/project/performance/query-budget-contract.json`, primary/default ownership in `config/database.php` and `docs/project/stack/runtime-environments.json`, safe primary-equivalent fallback for the optional read connection, no automatic read routing, and repository verification that these boundaries remain aligned.

For 25.0C specifically, acceptance requires repository-owned regional/traffic-control decision semantics with evidence states, side-effect-free health and drain rules, mutation-safe retry policy, explicit failback to the accepted direct Caddy/Compose topology, no automatic routing or infrastructure mutation, and an explicit Envoy deferral unless a declared trigger becomes true.

For 25.0D specifically, acceptance requires a repository-owned external-observability evaluation that records the demonstrated-gap trigger, expected operational value, privacy/retention constraints, cost/cardinality controls and vendor exit/fallback semantics; absent a demonstrated gap, external APM remains deferred and must not become a production dependency.

All Stage 25 acceptance criteria are satisfied subject to the final exact-head Auto Closure of this authored closure state before merge.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/OBSERVABILITY.md`
- `docs/operations/PRODUCTION_TOPOLOGY.md`
- `docs/operations/external-observability-evaluation.md`
- `docs/project/stack/production-service-baseline.json`
- `docs/project/engineering/external-systems-registry.json`
- Stage 24 operational-intelligence configuration/read model and accepted scorecard semantics
- `docs/project/performance/performance-contracts.json`
- `docs/project/performance/query-budget-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`

### Installed versions

Exact package/runtime versions remain lockfile-owned. Stage 25 retains the accepted PHP 8.5, Laravel 13, PostgreSQL 18, Redis, Caddy, Horizon and Pulse stack. Acceptance authorizes no new package, proxy, database service or external APM by itself.

### Official external sources

Official Caddy documentation is authoritative for the existing edge/TLS capability. Official PostgreSQL documentation is authoritative for PostgreSQL connection, replication and consistency semantics. Official Laravel documentation is authoritative for Laravel cache/runtime behavior. Cloudflare official documentation may inform CDN, Cache, Workers and Hyperdrive evaluation; adopted semantics must still be recorded in repository authority. External APM vendor documentation may inform an evidence-backed evaluation but cannot override SongChart security, privacy, product or verification authority.

### Native capability assessment

SongChart already has Caddy TLS/static delivery, Laravel HTTP/cache primitives, Redis, PostgreSQL 18, production Compose profiles, runtime health, Pulse/Horizon observability and the Stage 24 scale scorecard. These native owners are sufficient to define and verify scaling policy before any external edge/database/APM service is made a production dependency.

### Custom implementation justification

SongChart-specific work is limited to policy and decision composition that upstream products cannot know: route cacheability/freshness classes, canonical/provenance-sensitive bypass rules, evidence thresholds, primary/replica eligibility, regional/failure semantics, fallback behavior and provider-neutral scale decisions. Stage 25 prefers configuration/read-model/verifier changes over custom network infrastructure.

## Activation evidence

1. Stage 24.0 is accepted in repository authority.
2. Stage 24 PR #36 merged from exact verified head `6cb48bddf6101e7d55a933fa808d0e97a854e63d` into `main` as `d5eb48fd36ae3796de55201adbbf75c6d8ee99f4`.
3. Stage 24 final Auto Closure run `34924745305` (#520) passed PREPARE/QUALITY, PostgreSQL 18, frontend build, desktop/mobile browser smoke, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote.
4. Accepted-main workflow run `34925210011` passed quality, PostgreSQL 18, frontend build, desktop/mobile browser smoke and accepted-main provenance classification on `d5eb48fd36ae3796de55201adbbf75c6d8ee99f4`.
5. No existing open Stage 25 PR/work lease existed when activation began.

## Verification ownership

Repository-owned verification remains:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Stage 25 extends existing policy/verifier consumers only where required and introduces no second verification framework.

## Tests and verification

Tranches 25.0A through 25.0D have repository-owned exact-head verification evidence. The authored closure state must pass one fresh Auto Closure before PR #37 is merged so that acceptance metadata itself is covered by exact-head verification.

## Handoff

Stage 25 is closed at the authored authority level. No future stage or roadmap insertion is activated here. After the final exact-head closure and merge, the next action is a project-wide survey/review to agree future direction before any roadmap authority change.

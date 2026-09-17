# Stage 28.0 — Launch Readiness & Production Cutover

## Status

Active on the single `stage-28-launch-readiness` work lease from accepted `main@5db0b6377997d146d86b4fd8c5aedd0a66c189a3`. `docs/project/engineering/stage-plan.json` owns activation state and currently points to this contract with `28.0A — Launch Surface Audit & Critical Flow Closure` active. Stage 27 remains the accepted baseline.

## Goal

Ship the first production release as early as safely possible by closing only demonstrated launch blockers. Reuse the accepted public product, SEO, deployment, backup/restore, observability, provider, account and verification foundations instead of reopening them as redesign projects.

The launch priority order is:

`launch > reliability > critical correctness > discoverability > measurement > polish > expansion`.

Semantic wording, minor UX/UI inconsistency, non-critical visual polish and optional provider enrichment are post-launch work unless they prevent a primary user or operator flow from completing safely.

## Launch blocker definition

A finding blocks launch only when it materially causes one of the following:

- data loss, corruption or unsafe canonical mutation;
- security/privacy exposure or broken authorization;
- migration/deploy/rollback/backup-restore failure;
- production crash or unavailable primary public route;
- unusable search/entity/chart/account flow;
- materially broken canonical URL/indexability/sitemap/robots behavior;
- required queue/scheduler/runtime dependency cannot recover or degrade safely;
- approved public destination behavior violates an existing provider/compliance gate.

The following do not block launch by default: copywriting polish, terminology cleanup, minor spacing/typography inconsistencies, optional metadata gaps, optional provider integrations, recommendation/community/visitor-AI/RAG expansion, or broad design-system cleanup.

## Invariants

1. Repository authority remains primary over chat context and research notes.
2. Stage 28 adds no parallel deployment, SEO, provider, telemetry or verification subsystem when an accepted owner already exists.
3. Production public requests remain independent of live provider calls for core rendering.
4. PostgreSQL remains durable structured-data authority; Redis remains runtime coordination/cache/queue state.
5. Backup is accepted only with verified isolated restore evidence under the existing production authority.
6. Human promotion remains required for production cutover and irreversible actions.
7. Generated authority remains generator-owned.
8. Minor semantic/UX/UI debt follows `fix-while-touching`: fix low-risk defects on an already-touched surface; otherwise record them for post-launch stabilization.
9. New provider adapters, external SaaS, recommendations, visitor AI, dedicated RAG/vector infrastructure and architecture rewrites are non-goals unless a concrete launch blocker proves they are required.

## Tranches

### 28.0A — Launch Surface Audit & Critical Flow Closure

Active.

Goals:

- inventory the real public/operator launch surfaces from executable routes and current production authorities;
- prove the primary visitor path: home/search → canonical entity/chart → approved outbound destination;
- prove the authenticated saved-item/account path where enabled;
- prove the operator path: provider/import evidence → governed review/admission → public read;
- classify findings as `launch_blocker`, `post_launch`, or `insufficient_evidence`;
- implement only bounded fixes required for launch-critical completion.

### 28.0B — Production Safety & Recovery Closure

Goals:

- reuse Stage 19 production topology, health, deployment, queue/scheduler and backup/restore ownership;
- verify production environment preflight, migrations, secrets/config boundaries and runtime dependencies;
- verify rollback/degradation paths and isolated restore evidence;
- avoid introducing a new control plane or hosting abstraction.

### 28.0C — Discoverability & Public Trust Minimum

Goals:

- verify canonical URLs, robots, sitemap, public metadata/social presentation and structured-data behavior already owned by Stage 18.3;
- close only demonstrated launch-critical SEO/indexability defects;
- verify minimum public trust/contact/report/privacy surfaces that are actually required by the deployed product and current data handling;
- keep search-engine integrations and growth analytics evidence-gated.

### 28.0D — Production Candidate & Launch Closure

Goals:

- run production-equivalent verification on the exact release candidate;
- verify representative desktop/mobile public flows and production health;
- produce explicit launch/no-launch blocker evidence;
- close Stage 28 through the existing exact-head Auto Closure ownership;
- leave production promotion as an explicit human action.

## Reuse evidence already present

- Stage 18.3 owns canonical metadata, structured data, robots and sitemap behavior.
- Stage 19 owns Docker/Caddy production topology, health, deployment guidance, backup retention and verified restore semantics.
- Stage 23 owns representative public UX/mobile/accessibility/performance baseline.
- Stage 24 owns operational intelligence and health evidence composition.
- Stage 25 owns edge/cache/database/regional scale guardrails without mandatory extra infrastructure.
- Stage 27 owns bounded product-signal and authenticated saved-item baseline.

Stage 28 must verify these capabilities against launch reality rather than re-implement them.

## Explicit non-goals

- UI redesign or site-wide terminology cleanup.
- Wikidata, Discogs, Spotify, Last.fm, ListenBrainz or other new provider integration unless required by a proven launch blocker.
- Recommendation/community/native app/visitor AI.
- Dedicated vector database, generalized RAG subsystem or autonomous canonical mutation.
- Backstage, Storybook, Temporal, Kafka, Kubernetes, warehouse or another platform layer.
- New analytics SaaS by default.
- Infrastructure scale promotion without accepted evidence.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/SCOPE.md`
- `docs/project/docs/ARCHITECTURE.md`
- `docs/project/docs/URL_SEO.md`
- `docs/operations/PRODUCTION_TOPOLOGY.md`
- `docs/operations/edge-delivery-policy.md`
- `docs/project/domain/route-authority.json`
- `candidate-verification.json`
- existing launch-relevant application routes, tests and verification scripts

### Installed versions

Repository lockfiles, `composer.json`, `package.json` and container definitions are executable version authority. Stage 28 assumes the accepted Laravel 13 / PHP 8.5 / PostgreSQL 18 baseline and adds no package by default.

### Official external sources

No new external platform documentation is required to activate Stage 28. If a concrete launch blocker depends on Laravel, PostgreSQL, Caddy, Redis, Horizon, Pulse or another installed dependency, use the version-matched official upstream documentation for that dependency before introducing custom behavior. Third-party blog posts, chat context and generic deployment advice are not SongChart launch authority.

### Native capability assessment

Before adding code or infrastructure, inspect the accepted SongChart owners first: Laravel routing/auth/policies/queues/scheduler, PostgreSQL migrations and data boundaries, Redis/Horizon runtime ownership, existing Caddy/Docker production topology, Stage 18.3 SEO controllers/read models, Stage 19 backup/restore and deployment commands, Stage 24 operational read models, and existing browser/Auto Closure verification. Reuse these capabilities when they satisfy the blocker.

### Custom implementation justification

Custom Stage 28 code is allowed only for a demonstrated launch blocker that cannot be closed by configuration, existing application behavior or an accepted repository owner. Any custom implementation must be the smallest bounded change, attach to an existing owner, include focused regression coverage, preserve rollback/degradation behavior and avoid creating a second control plane, provider system, SEO system, analytics stack or deployment authority.

## Tests and verification

Use the repository-owned path only:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

GitHub Auto Closure remains the exact-head acceptance authority. Browser evidence is added only for representative launch-critical surfaces. Production promotion is not implied by source acceptance. Focused launch verification must reuse existing Feature/Architecture/Browser ownership and add tests only where a demonstrated blocker lacks regression coverage.

## Handoff rule

One umbrella branch/PR owns Stage 28. Findings discovered during audit do not create new stages or roadmap edits. Non-blocking semantic/UX/UI/provider/architecture findings are retained for evidence-driven post-launch stabilization. A later stage is not activated until Stage 28 is accepted, merged and the first production-launch decision is recorded.

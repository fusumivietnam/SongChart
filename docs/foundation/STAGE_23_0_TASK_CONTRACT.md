# Stage 23.0 — Public Product UX

## Status

Active. Stage 23 source implementation is authorized from accepted `main` SHA `84e05bed2cff63d5172e64b599e2f7e9d79971c5` after Stage 22 PR #30 was human-merged and accepted-main CI run `34624409179` passed quality, PostgreSQL, browser smoke, frontend build and accepted-main provenance. The dedicated Stage 23 work lease is `stage-23-public-product-ux`; tranche `23.0A` is the only active implementation tranche.

## Goal

Turn the accepted Stage 22 canonical data, provenance, chart and AI/control-plane foundations into a coherent public product experience. Stage 23 improves discovery, search, canonical entity detail, persisted chart presentation, provider routing, accessibility and mobile behavior without inventing popularity/ranking semantics or provider-specific canonical identity.

## Product-owner journeys

Primary journey authority: `docs/project/domain/product-user-journeys.json`.

Stage 23 directly owns refinement of:

- `visitor.discover`
- `visitor.follow_destination`

Account, editorial ingestion/admission and operator recovery remain outside the public-product implementation boundary unless a separately accepted cross-journey defect requires a bounded correction.

## Design authority

Public UI work must follow, in order:

1. `docs/ui/DESIGN_AUTHORITY.md`
2. `docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`
3. approved shared tokens/components
4. approved Phase 4/5/6/7/8 public UI contracts and preview patterns
5. page-specific implementation

No Stage 23 tranche may silently introduce a new visual direction.

## Invariants

1. Canonical SongChart entities remain provider-neutral.
2. Provider evidence and destinations remain provenance/context and outbound actions, never canonical primary identity.
3. Public pages may display only persisted or otherwise explicitly sourced chart/metric observations; no fabricated popularity, listener or ranking values.
4. Search ranking semantics remain owned by the existing search/application boundary; UI refinement must not invent an independent ranking algorithm.
5. Work, Recording, Release, Version, Artist/Group and Collection distinctions remain explicit.
6. Provider actions must preserve destination status, compliance, HTTPS/allowlist and disclosure rules.
7. Primary public metadata remains server-rendered; avoid unnecessary client hydration.
8. All production states must remain representable: loading, empty, error, partial data, stale data and degraded provider data where applicable.
9. Mobile behavior is deliberate and WCAG 2.2 AA remains the accessibility target.
10. Reusable UI patterns belong in shared components before broad page duplication.
11. Stage 23 must consume Stage 22 read/provenance contracts rather than bypassing them with direct provider-specific queries.
12. No schema/domain concept may be added only to satisfy presentation convenience.

## Tranches

### 23.0A — Discovery and search product shell

Status: active.

Goals:

- align homepage/discovery and search results with the approved search-first information hierarchy;
- preserve the canonical search URL/filter contract and existing search owner semantics;
- make entity type, primary context, verification/provenance state and navigation affordance consistently legible;
- improve empty/partial/degraded states without inventing results or ranking evidence;
- prove desktop and mobile behavior through the existing browser-smoke owner;
- preserve exact-head desktop/mobile screenshots as bounded visual-review evidence in the existing browser CI lane.

Primary surfaces:

- `resources/views/home.blade.php`
- `resources/views/search/index.blade.php`
- shared search/entity-result components already owned under `resources/views/components/`
- existing public search controller/query/read-model owners
- `tests/Browser/PublicCriticalSmokeTest.php`
- existing `.github/workflows/tests.yml` browser-smoke job for visual evidence publication

### 23.0B — Canonical entity detail experience

Status: planned.

Goals:

- refine Artist/Group, Recording, Work, Release/Release Group, Version and Collection detail pages around the shared identity/facts/relationships/provenance/provider system;
- keep entity-specific differences in catalog/read-model payloads instead of duplicating page architecture;
- make canonical relationships, source state and provider destinations understandable without admin-style density;
- preserve SEO/structured-data ownership and stable canonical URLs.

Primary surfaces:

- `resources/views/entities/`
- `resources/views/catalog/`
- shared entity/provider components
- existing public catalog/detail read-model owners

### 23.0C — Persisted chart and provenance UX

Status: planned.

Goals:

- present persisted Stage 22 chart observations with metric semantics, observation/provenance context and freshness state;
- distinguish unavailable/insufficient chart evidence from a valid zero or empty ranking;
- connect chart rows to canonical entity detail and approved destinations without provider identity leakage;
- prohibit fabricated chart, listener, popularity or engagement values.

Primary surfaces:

- `resources/views/charts/`
- existing chart/public read-model owners
- shared provenance and entity result components

### 23.0D — Mobile, accessibility, performance and stage closure

Status: planned.

Goals:

- normalize responsive behavior and public navigation across Stage 23 surfaces;
- verify minimum 44×44px touch targets, semantic headings, focus visibility, labels, landmark navigation and reduced-motion compatibility;
- protect primary server-rendered metadata and avoid unnecessary hydration/provider widgets;
- close Stage 23 through exact-head Auto Closure with PostgreSQL, frontend build and browser smoke evidence.

## Acceptance criteria

- Homepage/discovery and search conform to approved public design contracts on desktop and mobile.
- Search preserves existing canonical query/filter/ranking semantics and never fabricates metrics.
- Public detail pages use shared entity architecture and preserve canonical entity distinctions.
- Provider chooser/destination actions remain compliant, status-aware and externally disclosed.
- Persisted chart pages expose provenance/freshness/metric semantics without presenting unavailable evidence as observed data.
- Public routes retain stable canonical URLs and appropriate metadata/structured-data ownership.
- Required loading/empty/error/partial/stale/degraded states are represented where the owning read contract can produce them.
- Shared components are reused before adding page-specific markup or styling.
- No Stage 23 tranche introduces a provider-specific canonical identity, presentation-only schema, recommendation engine, social graph or visitor-facing AI assistant.
- Focused regression coverage and browser smoke prove desktop/mobile/accessibility-sensitive flows.
- Successful browser CI preserves exact-head visual-review screenshots for the bounded public surfaces owned by the active tranche.
- Generated repository projections remain PREPARE-owned.
- Each accepted tranche requires exact-current-head Auto Closure evidence before authority advances.

## Activation evidence

The Stage 23 activation gate is satisfied:

1. Stage 22.4 was accepted with exact-head Auto Closure evidence.
2. Stage 22 umbrella PR #30 was human-merged into `main`.
3. Resulting merge SHA is `84e05bed2cff63d5172e64b599e2f7e9d79971c5`.
4. Accepted-main CI run `34624409179` passed on that merge SHA.
5. Runtime repository state was re-resolved from `main`.
6. Dedicated branch `stage-23-public-product-ux` was created from the accepted merge SHA.
7. `stage-plan.json` advances to Stage 23.0 with exactly one active tranche: `23.0A`.

## Verification ownership

Reuse repository-owned verification only:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Browser behavior remains owned by the existing browser-smoke lane. Do not add a second frontend test harness or a Stage 23-specific verifier if an existing contract/Pest/browser owner can express the invariant. Visual screenshots are evidence produced by that owner, not a separate source of truth and not an independent pixel-diff gate.

## Explicit non-goals

- Recommendation engine.
- Social graph or follow graph.
- Visitor-facing AI assistant.
- Internal streaming/playback service.
- Provider-specific canonical entities.
- Fabricated ranking, listener, popularity or chart data.
- Presentation-only schema migrations.
- New frontend framework when Blade/Tailwind/Alpine/Livewire already owns the requirement.
- Autonomous release, merge or production mutation.

## Handoff

Stage 23 implementation proceeds only on the dedicated Stage 23 work lease. At session start, resolve live branch/PR/exact head and repository-derived context; chat history is never volatile project-state authority.

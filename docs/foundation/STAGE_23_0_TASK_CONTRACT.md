# Stage 23.0 — Public Product UX

## Status

Active. Stage 23 source implementation is authorized from accepted `main` SHA `84e05bed2cff63d5172e64b599e2f7e9d79971c5` after Stage 22 PR #30 was human-merged and accepted-main CI run `34624409179` passed quality, PostgreSQL, browser smoke, frontend build and accepted-main provenance. The dedicated Stage 23 work lease is `stage-23-public-product-ux`.

Accepted tranches:

- `23.0A` — exact head `e5dfc72e1b81753baa425bcdb5fb01f351b5899d`, Auto Closure run `34628234776`.
- `23.0B` — exact head `89582355a42fb93bba1268ec41f81eb6e297643e`, Auto Closure run `34630259521`.
- `23.0C` — exact head `1562011bb51f512a25620f66155ab4a625dd375b`, Auto Closure run `34767380205`.

Tranche `23.0D` is now the only active implementation tranche.

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

Status: accepted.

Acceptance evidence: exact head `e5dfc72e1b81753baa425bcdb5fb01f351b5899d`, Auto Closure run `34628234776`; PREPARE/QUALITY, PostgreSQL 18, production-built desktop/mobile browser smoke, frontend build, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote all passed.

### 23.0B — Canonical entity detail experience

Status: accepted.

Acceptance evidence: exact head `89582355a42fb93bba1268ec41f81eb6e297643e`, Auto Closure run `34630259521`; PREPARE/QUALITY, PostgreSQL 18, production-built desktop/mobile browser smoke with representative Group and Recording visual evidence, frontend build, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote all passed.

### 23.0C — Persisted chart and provenance UX

Status: accepted.

Acceptance evidence: exact head `1562011bb51f512a25620f66155ab4a625dd375b`, Auto Closure run `34767380205`; PREPARE/QUALITY, PostgreSQL 18, production-built desktop/mobile browser smoke with SHA-bound visual evidence, frontend build, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote all passed.

Accepted outcomes:

- public chart surfaces consume persisted chart observations/read models rather than deriving provider rankings in the view;
- metric semantics, units, observation timestamps, provenance/source context and freshness remain legible;
- unavailable evidence is distinct from valid observed zero or empty ranking states;
- chart rows retain canonical SongChart identity and routes while provider information remains evidence context;
- localized score presentation does not alter persisted values or calculation precision;
- representative chart states are covered through the existing production-built browser lane.

### 23.0D — Mobile, accessibility, performance and stage closure

Status: active.

Goals:

- normalize responsive behavior and public navigation across Stage 23 surfaces;
- verify minimum 44×44px touch targets, semantic headings, focus visibility, labels, landmark navigation and reduced-motion compatibility;
- verify fixed mobile navigation does not obscure actionable or primary content at viewport level;
- protect primary server-rendered metadata and avoid unnecessary hydration/provider widgets;
- preserve representative desktop/mobile browser evidence through the existing browser-smoke owner;
- close Stage 23 through exact-head Auto Closure with PostgreSQL, frontend build, browser smoke, canonical CLOSE and exact-tree preservation.

Primary surfaces:

- shared public layout/navigation and shared UI components;
- Stage 23 homepage, search, canonical entity detail and chart surfaces;
- existing CSS/Tailwind application styles and build owner;
- `tests/Browser/PublicCriticalSmokeTest.php` and existing feature/accessibility-sensitive owners;
- existing `.github/workflows/tests.yml` browser-smoke lane.

## Acceptance criteria

- Homepage/discovery and search conform to approved public design contracts on desktop and mobile.
- Search preserves existing canonical query/filter/ranking semantics and never fabricates metrics.
- Public detail pages use shared entity architecture and preserve canonical entity distinctions.
- Canonical identity, provenance/evidence and provider destinations are visually distinct and understandable without exposing internal/admin workflow density as the primary user experience.
- Provider chooser/destination actions remain compliant, status-aware and externally disclosed.
- Persisted chart pages expose provenance/freshness/metric semantics without presenting unavailable evidence as observed data.
- Public routes retain stable canonical URLs and appropriate metadata/structured-data ownership.
- Required loading/empty/error/partial/stale/degraded states are represented where the owning read contract can produce them.
- Shared components are reused before adding page-specific markup or styling.
- Public mobile navigation and fixed controls do not obscure primary content or required actions at representative viewport sizes.
- Touch targets, semantic landmarks/headings, labels, focus-visible behavior and reduced-motion behavior meet the repository's WCAG 2.2 AA target where applicable.
- Primary public metadata remains server-rendered and no unnecessary Stage 23 hydration layer is introduced.
- No Stage 23 tranche introduces a provider-specific canonical identity, presentation-only schema, recommendation engine, social graph or visitor-facing AI assistant.
- Focused regression coverage and browser smoke prove desktop/mobile/accessibility-sensitive flows.
- Successful browser CI preserves exact-head visual-review screenshots for the bounded public surfaces owned by the active tranche.
- Generated repository projections remain PREPARE-owned.
- Each accepted tranche requires exact-current-head Auto Closure evidence before authority advances.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/domain/product-user-journeys.json`
- `docs/ui/DESIGN_AUTHORITY.md`
- `docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`
- existing Phase 4/5/6/7/8 public UI contracts and preview patterns
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- existing public search/application read-model owners, chart read-model owners and shared Blade UI components

### Installed versions

Stage 23.0 introduces no package or runtime dependency. Exact versions remain owned by `composer.lock` and `package-lock.json`. The active baseline continues to use PHP 8.5, Laravel 13, PostgreSQL 18, Node 24, Tailwind CSS through the existing frontend build, Alpine/Livewire only where already justified, and Pest Browser through the repository's locked development dependencies.

### Official external sources

Stage 23.0 relies only on capabilities already present in the locked stack. Official Laravel documentation remains the external authority for Blade/server-rendered application behavior; official Pest Browser documentation remains the external authority for device emulation and screenshot capture; W3C/WAI WCAG 2.2 remains the accessibility reference. External documentation may validate framework capability but must not override SongChart's repository-owned product, domain, ranking, provenance or visual authority.

### Native capability assessment

Existing Blade/Tailwind components, the public search controller/application/read-model boundary, persisted chart read models, canonical public routes, Pest Browser, GitHub Actions artifact upload and the established browser-smoke lane already provide the required capabilities. Stage 23 therefore does not need React/Vue, a second search engine, a second browser harness, a visual-regression SaaS, a client-side provider query layer, a chart recomputation layer or a presentation-specific schema.

For 23.0D, native CSS/Tailwind responsive utilities, semantic HTML, browser focus behavior, media queries and Pest Browser viewport interaction are sufficient. No accessibility SaaS or second frontend runtime is justified unless a concrete repository gap is proven.

### Custom implementation justification

SongChart-specific composition is required because generic UI frameworks do not understand canonical music entity distinctions, verification/provenance semantics, governed provider destinations, persisted chart observation semantics or SongChart's prohibition on fabricated ranking/popularity data. Custom work is limited to Blade/component composition, existing read-model projection, bounded responsive/accessibility corrections and browser evidence. Visual screenshots are review evidence tied to an exact SHA, not a second design source of truth or an autonomous pixel-diff acceptance gate.

## Activation evidence

The Stage 23 activation gate is satisfied:

1. Stage 22.4 was accepted with exact-head Auto Closure evidence.
2. Stage 22 umbrella PR #30 was human-merged into `main`.
3. Resulting merge SHA is `84e05bed2cff63d5172e64b599e2f7e9d79971c5`.
4. Accepted-main CI run `34624409179` passed on that merge SHA.
5. Dedicated branch `stage-23-public-product-ux` was created from the accepted merge SHA.
6. Stage 23.0A was accepted by Auto Closure run `34628234776`.
7. Stage 23.0B was accepted by Auto Closure run `34630259521`.
8. Stage 23.0C was accepted by Auto Closure run `34767380205` on exact head `1562011bb51f512a25620f66155ab4a625dd375b`.
9. `stage-plan.json` exposes exactly one active tranche: `23.0D`.

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

## Tests and verification

Accepted evidence through 23.0C proves the discovery/search shell, representative canonical detail pages and persisted chart/provenance presentation on production-built desktop/mobile browser lanes with PostgreSQL and exact-head canonical closure.

Focused evidence for 23.0D must prove:

- fixed mobile navigation and sticky/fixed controls do not obscure primary actionable content at representative viewport sizes;
- public interactive controls meet the repository's minimum touch-target expectation where applicable;
- public pages preserve one coherent heading hierarchy and landmark structure;
- keyboard focus remains visible for public navigation, search/filter controls, provider actions and expandable provenance controls;
- reduced-motion preference does not make primary content or navigation unavailable;
- Stage 23 public pages retain server-rendered primary metadata and do not introduce unnecessary hydration;
- representative mobile/desktop browser flows pass with production-built assets and bounded visual evidence;
- PostgreSQL, frontend build, browser smoke, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote pass before 23.0D acceptance and Stage 23 closure.

Required verification remains owned by the existing entrypoints and CI topology:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Each tranche requires exact-current-head Auto Closure evidence before acceptance. Visual review augments, but does not replace, functional/browser/accessibility verification.

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

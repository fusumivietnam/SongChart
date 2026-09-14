# Stage 23.0 — Public Product UX

## Status

Accepted. Stage 23 was activated from accepted `main` SHA `84e05bed2cff63d5172e64b599e2f7e9d79971c5` after Stage 22 PR #30 was human-merged and accepted-main CI run `34624409179` passed. The Stage 23 work lease is `stage-23-public-product-ux`.

Accepted tranches:

- `23.0A` — exact head `e5dfc72e1b81753baa425bcdb5fb01f351b5899d`, Auto Closure run `34628234776`.
- `23.0B` — exact head `89582355a42fb93bba1268ec41f81eb6e297643e`, Auto Closure run `34630259521`.
- `23.0C` — exact head `1562011bb51f512a25620f66155ab4a625dd375b`, Auto Closure run `34767380205`.
- `23.0D` — exact head `fd538b0659296836a9c06d2cf3b19a34f8499493`, Auto Closure run `34798952416`.

No Stage 23 implementation tranche remains active. A later stage requires separate repository-authored activation authority.

## Goal

Turn the accepted Stage 22 canonical data, provenance, chart and AI/control-plane foundations into a coherent public product experience. Stage 23 improves discovery, search, canonical entity detail, persisted chart presentation, provider routing, accessibility and mobile behavior without inventing popularity/ranking semantics or provider-specific canonical identity.

## Product-owner journeys

Primary journey authority: `docs/project/domain/product-user-journeys.json`.

Stage 23 refines:

- `visitor.discover`
- `visitor.follow_destination`

Account, editorial ingestion/admission and operator recovery remain outside this public-product boundary unless separately authorized.

## Design authority

Public UI follows, in order:

1. `docs/ui/DESIGN_AUTHORITY.md`
2. `docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`
3. approved shared tokens/components
4. approved Phase 4/5/6/7/8 public UI contracts and preview patterns
5. page-specific implementation

No Stage 23 work may silently introduce a new visual direction.

## Invariants

1. Canonical SongChart entities remain provider-neutral.
2. Provider evidence and destinations remain provenance/context and outbound actions, never canonical primary identity.
3. Public pages display only persisted or explicitly sourced chart/metric observations; no fabricated popularity, listener or ranking values.
4. Search ranking semantics remain owned by the existing search/application boundary.
5. Work, Recording, Release, Version, Artist/Group and Collection distinctions remain explicit.
6. Provider actions preserve destination status, compliance, HTTPS/allowlist and disclosure rules.
7. Primary public metadata remains server-rendered; unnecessary client hydration is prohibited.
8. Empty, error, partial, stale and degraded states remain representable where the owning read contract can produce them.
9. Mobile behavior is deliberate and WCAG 2.2 AA remains the accessibility target.
10. Reusable UI patterns belong in shared components before broad page duplication.
11. Stage 23 consumes Stage 22 read/provenance contracts rather than bypassing them with provider-specific queries.
12. No schema/domain concept is added only for presentation convenience.

## Accepted tranches

### 23.0A — Discovery and search product shell

Accepted on exact head `e5dfc72e1b81753baa425bcdb5fb01f351b5899d`, run `34628234776`. Discovery/search UX, canonical query/filter/ranking ownership, empty/degraded states and desktop/mobile visual evidence passed repository-owned closure.

### 23.0B — Canonical entity detail experience

Accepted on exact head `89582355a42fb93bba1268ec41f81eb6e297643e`, run `34630259521`. Canonical identity, facts, relationships, provenance and governed provider destinations passed representative Group/Recording browser evidence and canonical closure.

### 23.0C — Persisted chart and provenance UX

Accepted on exact head `1562011bb51f512a25620f66155ab4a625dd375b`, run `34767380205`.

Accepted outcomes:

- public chart surfaces consume persisted observations/read models rather than recomputing provider rankings in views;
- metric semantics, units, timestamps, provenance/source context and freshness remain legible;
- unavailable evidence is distinct from valid observed zero or empty ranking states;
- chart rows retain canonical SongChart identity and routes while provider information remains evidence context;
- localized score presentation does not alter persisted values or calculation precision.

### 23.0D — Mobile, accessibility, performance and stage closure

Accepted on exact head `fd538b0659296836a9c06d2cf3b19a34f8499493`, Auto Closure run `34798952416`.

Accepted outcomes:

- fixed mobile navigation retains viewport clearance and minimum touch-target coverage;
- public search, canonical detail and chart surfaces pass repository-owned accessibility-sensitive browser checks;
- focus affordances and reduced-motion behavior remain protected by shared CSS/architecture ownership rather than transient pseudo-state assertions;
- public secondary text contrast satisfies the browser accessibility gate on subtle surfaces;
- canonical evidence definition-list semantics are valid;
- primary public metadata remains server-rendered without a new hydration layer;
- Stage 23 closes through the existing canonical verification topology without a second frontend or browser harness.

## Acceptance criteria

Stage 23 acceptance requires canonical public discovery/search behavior, stable canonical entity routes, provider-neutral identity, persisted chart/provenance semantics, compliant provider destinations, responsive mobile navigation, WCAG-sensitive landmarks/labels/focus/touch/reduced-motion behavior, server-rendered primary metadata, shared component reuse, desktop/mobile browser coverage, PostgreSQL 18, frontend build, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote.

Generated repository projections remain PREPARE-owned. No Stage 23 tranche introduces a recommendation engine, social graph, visitor-facing AI assistant, internal playback service, provider-specific canonical entity, fabricated ranking/popularity data, presentation-only schema migration or second frontend/browser harness.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/domain/product-user-journeys.json`
- `docs/ui/DESIGN_AUTHORITY.md`
- `docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- existing public search/read-model, chart read-model and shared Blade UI owners.

### Installed versions

Stage 23 introduces no new package or runtime dependency. Exact versions remain lockfile-owned. The accepted baseline continues to use the repository-locked PHP 8.5, Laravel 13, PostgreSQL 18, Node 24, Tailwind CSS, Livewire only where already justified and Pest Browser through the locked development dependencies.

### Official external sources

Official Laravel documentation remains the external authority for Blade/server-rendered application behavior; official Pest Browser documentation remains the external authority for device/browser behavior; W3C/WAI WCAG 2.2 remains the accessibility reference. External documentation validates framework capability but does not override SongChart repository-owned product, domain, ranking, provenance or visual authority.

### Native capability assessment

Existing Blade/Tailwind components, canonical search/read-model ownership, persisted chart read models, canonical public routes, Pest Browser and GitHub Actions artifact ownership were sufficient. Stage 23 did not require React/Vue, a second search engine, visual-regression SaaS, client provider query layer, chart recomputation layer, accessibility SaaS or a second browser harness.

### Custom implementation justification

SongChart-specific composition is required because generic UI frameworks do not understand canonical music entity distinctions, verification/provenance semantics, governed provider destinations, persisted chart observation semantics or the prohibition on fabricated ranking/popularity data. Custom work remains bounded to existing Blade/component composition, existing read models, responsive/accessibility corrections and SHA-bound browser evidence.

## Activation and closure evidence

1. Stage 22.4 was accepted and PR #30 was human-merged.
2. Accepted-main SHA is `84e05bed2cff63d5172e64b599e2f7e9d79971c5`; accepted-main CI run is `34624409179`.
3. Stage 23.0A was accepted by run `34628234776`.
4. Stage 23.0B was accepted by run `34630259521`.
5. Stage 23.0C was accepted by run `34767380205` on exact head `1562011bb51f512a25620f66155ab4a625dd375b`.
6. Stage 23.0D was accepted by run `34798952416` on exact head `fd538b0659296836a9c06d2cf3b19a34f8499493`.
7. `stage-plan.json` records Stage 23 as accepted with no active tranche.
8. The authority-transition head itself must pass normal exact-head Auto Closure before promotion/merge.

## Verification ownership

Repository-owned verification remains:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Browser behavior remains owned by the existing browser-smoke lane. Visual screenshots are SHA-bound review evidence, not a separate source of truth or pixel-diff acceptance gate.

## Tests and verification

Accepted evidence through `23.0D` proves discovery/search, representative canonical detail pages, persisted chart/provenance presentation, mobile navigation clearance and accessibility-sensitive public behavior on production-built desktop/mobile browser lanes with PostgreSQL 18 and canonical exact-head closure. The authority-transition head must again pass PREPARE/QUALITY, PostgreSQL, frontend build, browser smoke, exact-head classification, canonical CLOSE, exact-tree preservation and ready-to-promote.

## Handoff

Stage 23 implementation is closed. Repository and live GitHub authority must be resolved at session start; chat history is never volatile project-state authority. A later stage must be explicitly activated in repository authority before implementation begins.

# Stage 26.0 — Authority & Product Design Coherence

## Status

Active planning/implementation contract for the post-Stage-25 product-coherence workstream. The live work lease is branch `stage-26-authority-product-coherence`. Stage 25 remains the accepted baseline until Stage 26 passes repository-owned closure and is human-promoted.

The first bounded tranche is `26.0A — Authority Drift Closure`. Later tranches are declared here so dependencies are explicit, but they are not implicitly accepted and must not be implemented ahead of evidence.

## Goal

Turn the accepted Stage 25 technical baseline into a lower-drift product-development baseline before adding telemetry, retention or demand intelligence. Stage 26 reuses existing SongChart authorities, design-system inventory, Blade/Livewire stack and verification ownership. It does not create a second architecture authority, redesign framework or frontend runtime.

## Ponytail implementation posture

Stop at the first solution that fully holds:

1. remove or correct stale authority before creating new authority;
2. reuse existing repository contracts, design-system inventory, components and verifiers;
3. prefer Laravel/PHP/browser-native capability before an additional dependency;
4. use an already-approved dependency before SongChart-specific custom infrastructure;
5. introduce the minimum custom code required by a demonstrated gap.

Minimality must not weaken security, trust-boundary validation, data integrity, accessibility, canonical authority or required verification.

## Invariants

1. Repository authority remains primary over chat/agent memory and external-tool projections.
2. Accepted historical stage evidence is not rewritten merely to make current documentation look cleaner.
3. Current-state registries must not describe accepted Stage 22–25 work as future/unimplemented work.
4. `partial` remains valid where coverage is genuinely bounded; drift closure must not promote an edge to `verified` without implementation/test evidence.
5. A new registry or authority file is introduced only when it reduces recurring ambiguity or enables executable enforcement that existing owners cannot provide.
6. `docs/project/domain/route-authority.json` owns the canonical design-system HTTP family. The living inventory is served under `/development/design-system` with route names `development.design-system.*`; the retired `/ui-preview*` compatibility surface must not be reintroduced.
7. `docs/ui/DESIGN_AUTHORITY.md` remains the visual/interaction authority and must consume the canonical route family rather than inventing a parallel URL.
8. Blade/Livewire remains the default frontend stack. React/Inertia requires a separately evidenced interaction/state use case; Stage 26 does not authorize a rewrite.
9. No page-level visual invention is accepted outside approved tokens/components/patterns/layouts.
10. Generated files remain generator-owned and are not hand-edited.
11. Stage closure uses existing impact, candidate, canonical and Auto Closure ownership; no second verification framework is introduced.

## Tranches

### 26.0A — Authority Drift Closure

Current bounded tranche.

Scope:

- reconcile `docs/project/engineering/system-intersection-map.json` with accepted Stage 22.2 data-spine reality without overclaiming generalized coverage;
- remove stale Stage-specific future wording from current external-system decisions after accepted Stages 24/25;
- audit design/route authority against `routes/web.php` and `docs/project/domain/route-authority.json`, then make current design owners consume `/development/design-system` rather than the retired `/ui-preview*` alias;
- identify any remaining current-state registry statement that incorrectly points at an already-accepted stage as unfinished work;
- extend an existing verifier only if the same drift class is demonstrably recurring and machine-checkable.

Acceptance outcome: current authority describes current repository reality; historical evidence remains historical; no stale current-state statement falsely reopens accepted work or reintroduces retired route aliases.

### 26.0B — Executable UI Contract Closure

Not active until 26.0A is accepted.

Candidate scope:

- inventory existing semantic tokens, shared components, canonical design-system patterns and representative layouts;
- close only demonstrated gaps needed to make page implementation consume existing design authority consistently;
- prefer extending current machine-readable UI contracts over introducing parallel registries;
- add visual/browser evidence only for representative surfaces where current coverage is insufficient.

### 26.0C — Canonical Product Screens

Not active until 26.0B is accepted.

Candidate scope is limited to representative public/admin screens needed to prove the shared design system: search/zero-result, entity detail, chart, account and admin review/list surfaces. Reuse current screens where they already satisfy the contract; do not redesign for its own sake.

### 26.0D — Product-Coherence Closure

Not active until preceding tranches are accepted.

Validate responsive/accessibility/browser evidence, verify no page-level design divergence remains in representative flows, and close Stage 26 through existing exact-head verification.

## Explicit non-goals

- React/Inertia migration or SPA rewrite.
- New design-system repository or Storybook requirement.
- Generic capability-registry/authority-graph platform without a demonstrated consumer gap.
- Product telemetry, retention, demand intelligence or recommendation work; those depend on this stage rather than being bundled into it.
- New provider, AI runtime, CDN, multi-region, APM or data-platform adoption.
- Rewriting historical task contracts or validation records.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/engineering/system-intersection-map.json`
- `docs/project/engineering/external-systems-registry.json`
- `docs/project/domain/route-authority.json`
- `routes/web.php`
- `docs/ui/DESIGN_AUTHORITY.md`
- `docs/ui/PHASE_3_UI_PREVIEW.md`
- existing route/UI/design contracts and verification consumers
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

Stage 26 introduces no package or runtime dependency. Exact installed versions remain lockfile-owned. The accepted runtime baseline remains PHP 8.5, Laravel 13, PostgreSQL 18, Redis, Caddy, Livewire, Node 24 and the repository-owned Docker verification topology.

### Official external sources

No external product or provider is required to implement 26.0A. Laravel, PHP, PostgreSQL and browser documentation remain upstream references only where an existing repository contract needs clarification. Figma, Grafana Cloud, Google Search Console, Bing Webmaster, IndexNow and other external systems remain governed by `docs/project/engineering/external-systems-registry.json`; Stage 26.0A does not promote any of them into a new runtime dependency.

### Native capability assessment

The repository already contains the required owners for this tranche: machine-readable stage authority, system-intersection authority, external-system governance, route authority, Design Authority, canonical development design-system routes, generated project state, impact analysis and exact-head Auto Closure. The identified problem is stale or inconsistent authority text, not missing infrastructure. Existing repository capabilities are therefore sufficient.

### Custom implementation justification

SongChart-specific work is limited to reconciling repository-owned contracts that upstream frameworks cannot know: whether a stage is accepted, whether a partial edge is intentionally bounded, which external-system decision remains current, and which design/route authority is active. No generic drift platform, new registry layer or external dependency is justified for 26.0A unless repeated evidence later proves the existing verifiers cannot enforce a recurring invariant.

## Activation baseline

Stage 26 work begins from accepted `main` merge commit `d25dc667173bb47256a21db430b3c8e3ccd02212`, after Stage 25 acceptance, roadmap refresh merge, repository housekeeping, and a fresh local canonical verification reported as:

```text
Candidate verification contract passed.
[SongChart verify] Canonical verification PASSED.
[SongChart verify] PASSED.
```

No active product-stage branch existed when `stage-26-authority-product-coherence` was created. Dependabot maintenance PRs remain separate dependency-governance work and do not own Stage 26 product source.

## Tests and verification

Use the existing repository workflow:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Focused verification is used while implementing 26.0A. Exact-head Auto Closure is required before tranche acceptance/promotion. Generated authority is reconciled by its existing generator owner rather than edited manually. Official-source governance, candidate-stage identity, repository-state authority, route-authority consistency and generated-state consistency remain mandatory quality gates for this contract.

## Handoff rule

Only `26.0A` is currently actionable. Do not begin `26.0B` merely because it is listed here. First prove that authority drift is closed on the current tree and that any remaining `partial` state reflects an intentional bounded capability rather than stale stage wording.

# Stage 26.0 — Authority & Product Design Coherence

## Status

Active planning/implementation contract for the post-Stage-25 product-coherence workstream. The live work lease is branch `stage-26-authority-product-coherence`. Stage 25 remains the accepted stage baseline until Stage 26 passes repository-owned closure and is human-promoted.

`26.0A — Authority Drift Closure` is accepted from Auto Closure run 608 on source head `587f9490afaee4d77fdf8a50aba8680e17c26926` with effective prepared head `a6175258072bba18c53083401344758ee76be9f4`. The current bounded tranche is `26.0B — Executable UI Contract Closure`. Later tranches remain declared for dependency visibility only and must not be implemented ahead of evidence.

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

Accepted.

Delivered:

- reconciled `docs/project/engineering/system-intersection-map.json` with accepted Stage 22.2 data-spine reality without overclaiming generalized coverage;
- removed stale Stage-specific future wording from current external-system decisions after accepted Stages 24/25;
- audited design/route authority against `routes/web.php` and `docs/project/domain/route-authority.json`, then made current design owners consume `/development/design-system` rather than the retired `/ui-preview*` alias;
- extended the existing route-authority verifier to guard recurring canonical design-system reference drift;
- retained intentional `partial` capability states instead of falsely promoting them.

Acceptance evidence: Auto Closure run 608 passed PREPARE/QUALITY, PostgreSQL 18, production frontend build, desktop/mobile browser smoke, exact-head classification, canonical CLOSE, exact-tree preservation, final exact-head revalidation and ready-to-promote on effective prepared head `a6175258072bba18c53083401344758ee76be9f4`.

### 26.0B — Executable UI Contract Closure

Current bounded tranche.

Scope:

- inventory existing semantic tokens, shared components, canonical design-system patterns and representative layouts;
- close only demonstrated gaps needed to make page implementation consume existing design authority consistently;
- prefer extending current machine-readable UI contracts over introducing parallel registries;
- add visual/browser evidence only for representative surfaces where current coverage is insufficient;
- keep Blade/Livewire and the current design-system route family unless a concrete implementation gap proves they are insufficient.

Acceptance outcome: representative page implementations can consume one coherent executable UI contract without page-specific visual invention or a new frontend/design-system subsystem.

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
- owning public/admin design contracts
- existing semantic tokens/shared Blade components and design-system inventory
- existing route/UI/design contracts and verification consumers
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

Stage 26 introduces no package or runtime dependency. Exact installed versions remain lockfile-owned. The accepted runtime baseline remains PHP 8.5, Laravel 13, PostgreSQL 18, Redis, Caddy, Livewire, Node 24 and the repository-owned Docker verification topology.

### Official external sources

No external product or provider is required for 26.0B. Laravel/Blade/Livewire, browser and accessibility documentation remain upstream references only where an existing repository contract needs clarification. Figma remains governed by `docs/project/engineering/external-systems-registry.json` and is used only when an accepted design node materially improves bounded design-to-code work; it is not a prerequisite or application dependency.

### Native capability assessment

The repository already contains semantic tokens, shared Blade components, public/admin design contracts, the canonical `/development/design-system` inventory, route authority, browser/visual verification and Blade/Livewire runtime capability. The 26.0B task is therefore an inventory-and-gap-closure exercise, not a mandate to build a new design system or frontend runtime.

### Custom implementation justification

SongChart-specific implementation is justified only for concrete gaps between the current executable UI and existing SongChart contracts. Reuse and extension of existing tokens/components/patterns comes first. A new abstraction is not introduced unless at least several real consumers share the same semantics and the existing owners cannot express the requirement cleanly.

## Activation baseline

Stage 26 began from accepted `main` merge commit `d25dc667173bb47256a21db430b3c8e3ccd02212`. Tranche 26.0B begins only after accepted 26.0A evidence from Auto Closure run 608 was recorded in `stage-plan.json`.

Dependabot maintenance PRs remain separate dependency-governance work and do not own Stage 26 product source.

## Tests and verification

Use the existing repository workflow:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Focused verification is used while implementing 26.0B. Exact-head Auto Closure is required before tranche acceptance/promotion. Generated authority is reconciled by its existing generator owner rather than edited manually. Official-source governance, candidate-stage identity, repository-state authority, route-authority consistency, design-contract consistency and generated-state consistency remain mandatory quality gates for this contract.

## Handoff rule

Only `26.0B` is currently actionable. Do not begin `26.0C` merely because it is listed here. First inventory the current executable UI, reuse what already satisfies the authority, close only demonstrated contract gaps, and prove representative browser/visual evidence where current coverage is insufficient.

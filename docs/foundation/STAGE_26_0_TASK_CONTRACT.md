# Stage 26.0 — Authority & Product Design Coherence

## Status

Active planning/implementation contract for the post-Stage-25 product-coherence workstream. The live work lease is branch `stage-26-authority-product-coherence`. Stage 25 remains the accepted baseline until Stage 26 passes repository-owned closure and is human-promoted.

The first bounded tranche is `26.0A — Authority Drift Closure`. Later tranches are declared here so dependencies are explicit, but they are not implicitly accepted and must not be implemented ahead of evidence.

## Goal

Turn the accepted Stage 25 technical baseline into a lower-drift product-development baseline before adding telemetry, retention or demand intelligence. Stage 26 reuses existing SongChart authorities, UI inventory, Blade/Livewire stack and verification ownership. It does not create a second architecture authority, redesign framework or frontend runtime.

## Ponytail implementation posture

Stop at the first solution that fully holds:

1. remove or correct stale authority before creating new authority;
2. reuse existing repository contracts, UI preview, components and verifiers;
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
6. `docs/ui/DESIGN_AUTHORITY.md` remains the visual/interaction authority. The existing `/ui-preview` living inventory is reused while it remains present and governed.
7. Blade/Livewire remains the default frontend stack. React/Inertia requires a separately evidenced interaction/state use case; Stage 26 does not authorize a rewrite.
8. No page-level visual invention is accepted outside approved tokens/components/patterns/layouts.
9. Generated files remain generator-owned and are not hand-edited.
10. Stage closure uses existing impact, candidate, canonical and Auto Closure ownership; no second verification framework is introduced.

## Tranches

### 26.0A — Authority Drift Closure

Current bounded tranche.

Scope:

- reconcile `docs/project/engineering/system-intersection-map.json` with accepted Stage 22.2 data-spine reality without overclaiming generalized coverage;
- remove stale Stage-specific future wording from current external-system decisions after accepted Stages 24/25;
- audit current design/route authority references against repository reality and leave valid `/ui-preview` references intact rather than deleting them based on stale conversational assumptions;
- identify any remaining current-state registry statement that incorrectly points at an already-accepted stage as unfinished work;
- extend an existing verifier only if the same drift class is demonstrably recurring and machine-checkable.

Acceptance outcome: current authority describes current repository reality; historical evidence remains historical; no stale current-state statement falsely reopens accepted work.

### 26.0B — Executable UI Contract Closure

Not active until 26.0A is accepted.

Candidate scope:

- inventory existing semantic tokens, shared components, UI-preview patterns and representative layouts;
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

## Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/engineering/system-intersection-map.json`
- `docs/project/engineering/external-systems-registry.json`
- `docs/ui/DESIGN_AUTHORITY.md`
- `docs/ui/PHASE_3_UI_PREVIEW.md`
- existing route/UI/design contracts and verification consumers
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

## Activation baseline

Stage 26 work begins from accepted `main` merge commit `d25dc667173bb47256a21db430b3c8e3ccd02212`, after Stage 25 acceptance, roadmap refresh merge, repository housekeeping, and a fresh local canonical verification reported as:

```text
Candidate verification contract passed.
[SongChart verify] Canonical verification PASSED.
[SongChart verify] PASSED.
```

No active product-stage branch existed when `stage-26-authority-product-coherence` was created. Dependabot maintenance PRs remain separate dependency-governance work and do not own Stage 26 product source.

## Verification ownership

Use the existing repository workflow:

```bash
./songchart ai status --json
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Use focused verification while implementing 26.0A; require exact-head Auto Closure before tranche acceptance/promotion. Generated authority must be reconciled by its existing owner rather than edited manually.

## Handoff rule

Only `26.0A` is currently actionable. Do not begin `26.0B` merely because it is listed here. First prove that authority drift is closed on the current tree and that any remaining `partial` state reflects an intentional bounded capability rather than stale stage wording.

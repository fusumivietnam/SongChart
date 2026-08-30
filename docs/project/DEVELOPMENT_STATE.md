# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.3 — Public Metadata & SEO Readiness` merged to `main` via PR #11 after exact-head canonical closure passed.
- Stage `18.3.1 — Verification & AI Workflow Convergence` merged to `main` via PR #12.
- Stage `18.4 — Admin Completion & Operational Convergence` merged to `main` via PR #13 from exact canonical-closed head `ac4ea17998a0faef6fc265abbe557a976e6e5610`; accepted merge commit `2ed9e6d3a5fb8cef21fc69dd61317dceca8f5e94`.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub Codespaces is the preferred remote adapter.

## Current stage

- Stage `18.5 — Public Product / Frontend Release Pass`
- Branch: `stage-18.5-public-product-frontend-release-pass`
- Base: accepted Stage 18.4 merge commit `2ed9e6d3a5fb8cef21fc69dd61317dceca8f5e94`.
- Candidate closure: pending.
- Strategy: release-pass first. Improve existing public surfaces in bounded slices; do not turn 18.5 into an architecture rewrite or provider-expansion program.

## Stage map

```text
18.5 PUBLIC PRODUCT / FRONTEND RELEASE PASS
        |
        +--> [IN PROGRESS] homepage + public IA baseline
        |                    `--> skip-navigation accessibility slice committed
        |
        +--> [NEXT] catalog/search UX + state coverage
        |
        +--> [NEXT] canonical entity-page convergence
        |
        +--> [NEXT] responsive/mobile + accessibility pass
        |
        +--> [NEXT] performance/Core Web Vitals evidence
        |
        +--> [NEXT] loading/empty/error + visual SEO polish
        |
        `--> [FINAL] release QA -> candidate -> canonical -> exact-head delivery
```

## Done

- Stage 18.4 accepted and merged through PR #13.
- Stage 18.5 branch created directly from the accepted Stage 18.4 merge commit.
- Stage 18.5 task contract and validation report initialized.
- Existing homepage/public-shell inventory confirms a mature search-first public surface already exists; 18.5 should converge and harden it rather than replace it.
- First concrete release gap closed in source: public shell now exposes a keyboard skip link to the existing main-content landmark, with focused homepage regression coverage.

## In progress

### Homepage + public information architecture baseline

Inventory the existing homepage, frontend shell and primary public navigation against Stage 18.5 acceptance criteria. Add only concrete release-quality edges around accessibility, responsive behavior, truthful states and navigation consistency.

Decision rule:

```text
RELEASE ACCEPTANCE EDGE
        |
        v
EXISTING PUBLIC SURFACE
        |
        +--> sufficient ----> record evidence, keep source
        +--> partial -------> smallest coherent UX/accessibility fix
        `--> missing -------> accepted minimal public use case first
```

## Current blockers / risks

- Public polish must not introduce fabricated popularity, recommendation or provider-quality signals.
- Search/catalog changes must preserve deterministic ranking/facets and canonical URL authority.
- Entity-page convergence must preserve entity-specific semantics and provenance rather than flatten all entity types into one generic template.
- Responsive/mobile improvements must not hide required navigation or primary search/actions.
- Visual SEO polish must not duplicate metadata/structured-data authority already accepted in Stage 18.3.
- Performance work should be evidence-driven; no speculative caching/query framework.
- Engineering graph/navigation improvements remain cross-stage support only and must not expand 18.5 scope.

## Latest focused evidence

- Stage 18.4 candidate/canonical/close passed on exact head `ac4ea17998a0faef6fc265abbe557a976e6e5610` before PR #13 merge.
- PR #13 merged into `main` at `2ed9e6d3a5fb8cef21fc69dd61317dceca8f5e94`.
- Stage 18.5 task contract bootstrap commit: `e2277305222976125f63c0b01a3efd8223a2d13e`.
- Public skip-navigation source commit: `738155f7e18f2f18a082167d05eaebce746cf63a`.
- Focused homepage regression commit: `abb139d41838c8421a10c619d296751d2e3de400`.
- Local/Codespaces verification for the first slice is pending.

## Next required action

1. Sync Codespaces to `stage-18.5-public-product-frontend-release-pass`.
2. Run planned/actual impact for the first public-shell/homepage slice.
3. Run `tests/Feature/HomePageTest.php`, shared-shell/public accessibility regressions, Pint and PHPStan as impacted.
4. If green, continue inventory of homepage/public IA before touching catalog/search.
5. Commit only bounded release gaps; update this checkpoint as evidence/next action changes.

## Documentation checkpoint discipline

For every logical implementation slice:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` as durable onboarding/overview, not current-stage state storage;
- keep `docs/project/DEVELOPMENT_STATE.md` as the operational current-state owner;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- keep completed-stage chronology in `docs/project/DEVELOPMENT_HISTORY.md` after governed acceptance;
- use the AI learning ledger only for reusable development evidence; promote durable rules into their owning authority;
- never duplicate workflow authority into model-specific instruction files.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift. Prefer the secret-redacted `./songchart ai doctor` bundle over manually copying raw environment/log output.

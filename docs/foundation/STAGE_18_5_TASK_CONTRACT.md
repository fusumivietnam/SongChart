# Stage 18.5 Task Contract — Public Product / Frontend Release Pass

Status: active task contract.

## Goal

Bring SongChart's public product surfaces to release-ready quality without changing canonical/provider semantics or turning this stage into an architecture rewrite.

## Non-goals

- No provider-breadth expansion unless a public release use case cannot be completed with approved existing providers.
- No new social/personalization product program.
- No production deployment topology; that remains in 19.x.
- No redesign of Admin except incidental shared-shell fixes.
- No weakening accessibility, SEO, authorization, PostgreSQL, performance, candidate, or canonical gates.
- No mass source-tree/layer refactor.

## Acceptance criteria

- Public information architecture makes homepage, search/catalog and canonical entity paths understandable and internally consistent.
- Homepage provides a clear primary search/discovery entry point and truthful product positioning without fabricated popularity or provider claims.
- Catalog/search UX handles normal, empty, invalid and paginated states with accessible semantics and deterministic navigation.
- Artist/Group/Release/Recording/Work canonical pages use a coherent public presentation system while preserving entity-specific semantics and provenance.
- Public shell and core surfaces are responsive on narrow/mobile layouts without hiding required navigation or primary actions.
- Keyboard/focus, heading hierarchy, form labels, landmarks and status/error messaging satisfy the repository accessibility baseline.
- Release-critical images/assets/layout behavior avoid obvious Core Web Vitals regressions; new work remains within existing performance/query-budget authorities.
- Loading, empty, unavailable and error states are explicit and do not fabricate data.
- Existing metadata/SEO contracts remain canonical; visual SEO polish must not create duplicate routes or conflicting structured metadata.
- Every changed public surface has focused regression coverage and passes impact, Pint/PHPStan where applicable, stage candidate and canonical closure.

## Primary surfaces

- `resources/views/home.blade.php`
- `resources/views/layouts/frontend.blade.php`
- `resources/views/search/**`
- `resources/views/catalog/**`
- `resources/views/entities/**`
- shared public components under `resources/views/components/**`
- public controllers/read models only when a concrete release UX gap requires data changes
- public feature/architecture tests and existing SEO/performance authorities

## Stage map

```text
18.5 PUBLIC PRODUCT / FRONTEND RELEASE PASS
        |
        +--> [FIRST] homepage + public IA baseline
        +--> [NEXT] catalog/search UX + state coverage
        +--> [NEXT] canonical entity-page convergence
        +--> [NEXT] responsive/mobile + accessibility pass
        +--> [NEXT] performance/Core Web Vitals evidence
        +--> [NEXT] loading/empty/error + visual SEO polish
        `--> [FINAL] release QA -> candidate -> canonical -> exact-head delivery
```

## Implementation discipline

- Inventory existing behavior before adding code.
- Prefer the smallest coherent presentation/data edge that closes a release gap.
- Preserve provider-neutral canonical identity, provenance, search determinism and existing public URL authority.
- Reuse existing Blade/UI components before creating new component systems.
- Do not create popularity, recommendation or ranking signals without governed source evidence.
- Controllers remain transport adapters; data changes stay behind existing application/read-model boundaries.

## Verification plan

- `./songchart impact <planned paths...>` before each coherent slice.
- Focused public feature/architecture tests during implementation.
- `./songchart impact --diff` after each coherent slice.
- `./songchart reconcile` only when registered generated inputs change.
- `./songchart audit` before closure.
- `./songchart candidate` then `./songchart close` on the exact committed tree.
- Any tracked change after canonical PASS invalidates closure evidence.

## Delivery

- Branch: `stage-18.5-public-product-frontend-release-pass`.
- Base: accepted Stage 18.4 merge commit `2ed9e6d3a5fb8cef21fc69dd61317dceca8f5e94`.
- One PR for the stage after exact-head canonical closure.

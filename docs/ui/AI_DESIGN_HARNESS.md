# SongChart AI Design Harness

Status: advisory integration contract under `docs/ui/DESIGN_AUTHORITY.md`.

## Purpose

Use external AI design skills such as Impeccable to improve critique, responsive adaptation, accessibility review, hardening and visual polish without creating a second SongChart design authority or verification authority.

## Authority order

```text
PROJECT_AUTHORITY.md / AGENTS.md
        ↓
docs/ui/DESIGN_AUTHORITY.md
        ↓
owning frontend/admin design contract
        ↓
approved tokens/components/UI-preview patterns
        ↓
AI design harness (this document)
        ↓
Impeccable or other external design guidance
```

When generic design guidance conflicts with a SongChart contract, SongChart wins. External guidance never silently changes entity terminology, provenance/provider disclosure, route behavior, accessibility invariants, canonical identity semantics, ranking claims or approved design tokens.

## Impeccable integration

Reviewed upstream: `pbakaus/impeccable`, Apache-2.0. Upstream documents project-scoped GitHub Copilot skills under `.github/skills/` and commands including `critique`, `audit`, `polish`, `harden`, `adapt`, `optimize`, `layout`, `typeset` and `clarify`.

SongChart intentionally integrates a project-local adapter skill instead of vendoring the upstream repository or adding a git submodule. This avoids a second dependency/update lifecycle and keeps SongChart authorities explicit.

Preferred uses:

- `critique`: hierarchy, clarity and product comprehension;
- `audit`: accessibility, responsive and frontend technical-quality findings;
- `harden`: empty/error/overflow/i18n/edge-state robustness;
- `adapt`: narrow/mobile/device adaptation;
- `polish`: final visual consistency against approved SongChart patterns;
- `optimize`: frontend performance suggestions that remain subject to existing performance authorities.

Generic anti-pattern guidance is advisory only. It must not override an intentional SongChart typography, color, spacing or component decision that is already governed.

## Required AI workflow

Before using the design harness, an AI agent must:

1. read `docs/ui/DESIGN_AUTHORITY.md`;
2. read the owning public/admin design contract;
3. inspect the existing shared component/token/UI-preview implementation;
4. identify the exact target surface and current behavior;
5. use Impeccable-style guidance only to shape or review a bounded change;
6. map actionable findings back to existing SongChart component/accessibility/performance owners;
7. run focused SongChart tests and GitHub CI; external detector output is supporting evidence, never canonical closure evidence.

## Forbidden behavior

- Do not run an external `init` flow that creates `PRODUCT.md` or `DESIGN.md` as competing SongChart authorities.
- Do not auto-apply visual redesigns across unrelated surfaces.
- Do not introduce fabricated metrics, popularity or recommendation signals.
- Do not change domain/provider semantics from a visual critique.
- Do not promote external detector output into canonical verification without a SongChart owner, machine contract/routing and permanent regression.
- Do not commit ephemeral screenshots/runtime cache from external design tools unless an owning SongChart contract explicitly requires a reviewed artifact.

## Verification role

The harness is advisory. SongChart focused tests, accessibility/performance authorities, GitHub Actions, candidate and canonical verification remain release-authoritative.

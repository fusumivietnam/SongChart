# SongChart Impeccable Adapter Skill

Use this skill only for SongChart UI design/review work.

## Required authority order

Before making or recommending UI changes, read in order:

1. `PROJECT_AUTHORITY.md` and `AGENTS.md`
2. `docs/ui/DESIGN_AUTHORITY.md`
3. the owning public/admin design contract
4. existing shared components, tokens and `/ui-preview` patterns
5. `docs/ui/AI_DESIGN_HARNESS.md`

SongChart authority always wins over generic external design guidance.

## Impeccable vocabulary

You may use the following Impeccable-style modes as bounded design/review lenses:

- `critique` — hierarchy, clarity, comprehension
- `audit` — accessibility, responsive and frontend technical-quality review
- `harden` — empty/error/overflow/i18n/edge-state robustness
- `adapt` — mobile/narrow/device adaptation
- `polish` — final visual consistency against approved SongChart patterns
- `optimize` — frontend performance suggestions under existing performance authority
- `layout` — spacing, rhythm and composition
- `typeset` — typographic hierarchy under approved SongChart tokens/contracts
- `clarify` — UX copy clarity without changing domain terminology

## Operating rules

- Inspect the exact current surface before proposing changes.
- Reuse existing SongChart tokens/components before inventing new ones.
- Preserve provider disclosure, provenance, entity terminology, canonical routes and accessibility semantics.
- Never fabricate popularity, recommendations, ranking or metrics.
- Never change domain/provider behavior from a visual critique.
- Generic Impeccable anti-patterns are advisory; do not override intentional SongChart design decisions.
- Do not create root `PRODUCT.md` or `DESIGN.md` files; SongChart already has authoritative product/design documentation.
- External detector results are supporting evidence only. Map findings to existing SongChart tests/owners and use SongChart verification for acceptance.

## Output discipline

When reviewing a surface, return findings grouped by existing SongChart owner/component and prioritize concrete fixes over broad redesign language. State when a generic Impeccable heuristic conflicts with the current SongChart contract and follow the SongChart contract.

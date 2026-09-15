# Phase 3 — UI Preview

Status: implemented in Starter v10.2.

## Purpose

`/development/design-system` is the living inventory for the approved SongChart UI system. It validates contracts, semantic tokens, shared Blade components, composed frontend/admin patterns and required data states before those patterns are copied into feature pages.

The former `/ui-preview*` compatibility surface is retired. It is not a public product page, a theme gallery or a source of live provider data.

## Routes

- `/development/design-system` → foundations
- `/development/design-system/components`
- `/development/design-system/patterns`
- `/development/design-system/states`
- `/development/design-system/admin`

The routes are available in `local` and `testing`. Outside those environments, `DESIGN_LAB_ENABLED=true` is required.

## Inventory sections

1. **Foundations** — semantic color, typography, spacing, radius and elevation.
2. **Components** — approved actions, form controls, badges and modal.
3. **Patterns** — search-first composition, entity result rows and explicit provider routing.
4. **States** — loading, empty, error and partial-data behavior.
5. **Admin** — attention-first work, operational metrics and secondary provider-health composition.

## Governance rules

- Read the relevant design contract before editing preview code.
- Extend shared primitives before adding duplicated page-level markup.
- Every new reusable UI pattern must be represented in the preview.
- Preview examples must use deterministic local fixtures only.
- Never call provider APIs, load remote media or imply in-site streaming.
- Preview remains `noindex,nofollow`.
- Validate keyboard focus, labels, touch targets and responsive layouts.
- A preview example does not approve a new visual direction by itself; contracts remain authoritative.

## Acceptance criteria

- Each section has a stable named route under `development.design-system.*`.
- Unknown sections return 404.
- The preview is navigable on desktop and mobile.
- Breakpoint visibility is inspectable in the top bar.
- Public and admin design semantics remain visually separate.
- Loading, empty, error and partial-data states are represented.
- Provider routing disclosure is explicit.
- No database or provider connection is required to render the preview.

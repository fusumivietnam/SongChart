# Engineering Rules

## PHP

- `declare(strict_types=1);`
- Typed properties, parameters and return values.
- Enums for bounded states.
- Immutable DTOs at integration boundaries.
- No business logic in Blade templates.
- Controllers coordinate; Actions perform use cases.
- Avoid service-locator usage.
- Avoid model observers for critical invisible workflows.

## Laravel

- Form Requests for validation.
- Policies/Gates for authorization.
- Jobs must be idempotent.
- Transactions wrap multi-write invariants.
- Events represent completed facts, not commands.
- Eager-load intentionally.
- Pagination for unbounded lists.
- Rate-limit auth, search and external-trigger endpoints.

## Frontend

- Server-rendered HTML by default.
- Livewire for meaningful interaction, not static content.
- Alpine for local UI state.
- Semantic color tokens; no arbitrary legacy color values in feature code.
- Keyboard navigation and visible focus.
- WCAG AA contrast target.
- Components must support light and dark themes.

## Dependencies

A package needs:
- clear owner;
- active maintenance;
- compatible license;
- security history review;
- exit strategy.

## Comments

Comments explain why, constraints or policy—not obvious syntax.

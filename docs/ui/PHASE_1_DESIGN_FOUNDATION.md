# Phase 1 — Design Foundation

Status: implemented in Starter v9.

## Delivered

### Semantic tokens

- public frontend colors;
- admin foundation colors;
- typography family;
- spacing scale;
- radius scale;
- shadow scale;
- content/container sizing;
- focus and reduced-motion behavior;
- compatibility aliases for legacy views.

### Shared Blade primitives

```text
resources/views/components/ui/
├── alert.blade.php
├── badge.blade.php
├── button.blade.php
├── card.blade.php
├── empty-state.blade.php
├── icon-button.blade.php
├── input.blade.php
├── modal.blade.php
├── skeleton.blade.php
└── textarea.blade.php
```

### UI preview

```text
/ui-preview
```

The route is available only when `DESIGN_LAB_ENABLED=true`.

It validates:

- semantic colors;
- typography hierarchy;
- button variants;
- form states;
- entity badges;
- alerts;
- skeletons;
- empty states;
- modal behavior.

## Implementation rules

- New pages must use semantic variables or approved component variants.
- Raw colors in new feature views are prohibited unless documenting a provider brand.
- Shared primitives must be extended before duplicating markup in feature pages.
- Light-first remains the canonical public direction.
- Admin-specific composition belongs to Phase 2, but its semantic token layer is reserved now.

## Completion criteria

- PHP and Blade source pass syntax/static inspection.
- Vite can resolve Tailwind and Alpine after dependency installation.
- `/ui-preview` renders in local/staging.
- Components expose disabled, error and accessible labeling where applicable.
- Focus and reduced-motion behavior are globally available.

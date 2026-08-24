# Phase 2 — Shared Shells

Status: implemented.

## Public frontend shell

Files:

- `layouts/frontend.blade.php`
- `components/shell/frontend-header.blade.php`
- `components/shell/mobile-bottom-nav.blade.php`

Provides desktop navigation, global search, account controls, responsive container and mobile bottom navigation.

## Admin shell

Files:

- `layouts/admin.blade.php`
- `components/admin/sidebar.blade.php`
- `components/admin/topbar.blade.php`
- `components/admin/page-header.blade.php`

Provides dark sidebar, mobile drawer, light workspace, global admin search, notifications and account identity.

## Compatibility

`layouts/app.blade.php` remains a compatibility alias to `layouts.frontend`. New pages must choose `layouts.frontend` or `layouts.admin` explicitly.

## Preview routes

When `DESIGN_LAB_ENABLED=true`:

- `/shell-preview/frontend`
- `/shell-preview/admin` (admin authentication required)

## Phase 2 acceptance

- frontend and admin have separate shells;
- mobile frontend has five labeled navigation items;
- admin sidebar becomes a mobile drawer;
- all shell controls have accessible labels;
- production pages can compose existing Phase 1 primitives;
- no shell depends on live provider APIs.

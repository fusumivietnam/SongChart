# Technology Stack

This page is a human-readable overview. Current dependency and capability authority lives in `docs/project/stack/package-registry.json`, `docs/project/stack/stack-manifest.json`, the dependency manifests, and their lockfiles. Do not duplicate package inventories or exact resolved versions here.

## Runtime

- PHP 8.5.
- Laravel 13.
- PostgreSQL 18.x; major 18 is the release-authoritative database baseline.
- Redis for queue/cache runtime.
- Node.js 24 for frontend build and browser tooling.
- Composer 2.

## Application

- Blade for server-rendered page composition.
- Livewire 4 for meaningful server-backed interaction.
- Alpine.js for local, ephemeral client-side state.
- Tailwind CSS 4.2 with semantic design tokens.
- Laravel Fortify for authentication backend.
- Laravel HTTP client for provider transport unless an approved capability owner replaces it.

## Operations

- Laravel Horizon is the primary Redis queue supervisor and queue-operations surface.
- Laravel Pulse provides application-level operational observability.
- Vite owns frontend asset compilation.
- Docker Compose is the primary Linux/WSL2 development runtime and canonical verification runtime.
- GitHub Codespaces is the supported remote development adapter over the same repository/runtime contract.
- Native Windows execution and Laragon are retired execution paths.

## Quality

- Pest 4 for unit, feature, architecture, and browser verification.
- Laravel Pint for formatting.
- Larastan/PHPStan for static analysis.
- GitHub Actions Auto Closure for exact-head PR verification.
- Repository verification topology and generated authority remain governed by their machine-readable contracts.

## Ownership

Use `docs/project/stack/STACK_OVERVIEW.md` and `docs/project/stack/CAPABILITY_OWNERSHIP.md` before changing a stack owner. Prefer Laravel/framework or existing approved capability owners before adding custom infrastructure or dependencies.

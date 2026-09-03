# Technology Stack Overview

## Authority

This directory is the canonical authority for SongChart framework, package and capability ownership decisions. Read it before adding dependencies, infrastructure abstractions or framework-adjacent code.

## Runtime baseline

- PHP: `^8.5` from `composer.json`.
- Laravel: `^13.0` from `composer.json`; resolved version is owned by `composer.lock`.
- Node.js: Node 24 LTS is the development, CI and canonical build baseline.
- npm: use the version bundled with the approved Node.js 24 runtime.
- Local development: Docker Engine/Compose v2 on Linux or WSL2 through the repository `songchart` CLI.
- Remote development: GitHub Codespaces is an optional Docker development adapter through the same `songchart` CLI. It uses `compose.dev.yml` plus `compose.codespaces.yml`, does not require local `mkcert`/Caddy TLS, exposes only app port `8000` to the private Codespaces forwarding proxy, and never auto-starts SongChart services merely because a Codespace opens.
- Native Windows/Laragon development paths are retired and are not engineering authority.
- CI: GitHub Actions.
- Database test/release authority: PostgreSQL; SQLite is optional compatibility-only.

## Backend

- Laravel modular monolith.
- Eloquent ORM and query builder.
- Fortify for authentication and two-factor authentication.
- Gates and Policies for authorization.
- Form Requests for HTTP validation.
- Actions for application use cases.
- Laravel Queue, Jobs and Scheduler for asynchronous work.
- Laravel HTTP client for provider transport until a bounded integration-package decision replaces it.

## Frontend

- Blade server-rendered HTML by default.
- Livewire for meaningful server-driven interaction.
- Alpine.js for local interface state.
- Vite for asset compilation.
- Tailwind CSS for the design system.

## Quality and development intelligence

- Pest/PHPUnit for tests.
- Laravel Pint for formatting.
- Larastan/PHPStan for static analysis.
- Composer scripts as the canonical verification interface.
- Project Context is deterministic generated repository authority.
- Project Intelligence is an eventual-consistency structural snapshot exposed through `./songchart artisan project:intelligence --json`; source scanning/rebuild is forbidden in HTTP requests.
- Technology/package decisions must use `docs/project/engineering/technology-evaluation-contract.json` and the current watchlist before adding custom framework-adjacent code.

## Default decision

Laravel first-party first, then a mature documented package, then genuinely SongChart-specific custom code. Package adoption must retire or prevent more custom surface than it adds; permanent dual implementations are not accepted.

## Canonical verification baseline

- PHP 8.5
- Laravel 13
- PostgreSQL major 18; canonical container reference is PostgreSQL 18.4
- Redis
- Node 24 LTS
- Composer/npm dependencies restored from repository lockfiles
- Linux/WSL2 Docker is the canonical local runtime; GitHub Codespaces is the remote adapter over the same Docker/CLI contract

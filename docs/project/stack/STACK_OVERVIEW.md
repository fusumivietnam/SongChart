# Technology Stack Overview

## Authority

This directory is the canonical authority for SongChartWeb framework, package and capability ownership decisions. Read it before adding dependencies, infrastructure abstractions or framework-adjacent code.

## Runtime baseline

- PHP: `^8.5` from `composer.json`.
- Laravel: `^13.0` from `composer.json`; resolved version is owned by `composer.lock`.
- Node.js: CI/runtime version is owned by the workflow and deployment environment.
- npm: use the version bundled with the approved Node.js runtime.
- Local development: Windows Laragon.
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
- Laravel HTTP client for provider transport.

## Frontend

- Blade server-rendered HTML by default.
- Livewire for meaningful server-driven interaction.
- Alpine.js for local interface state.
- Vite for asset compilation.
- Tailwind CSS for the design system.

## Quality toolchain

- Pest/PHPUnit for tests.
- Laravel Pint for formatting.
- Larastan/PHPStan for static analysis.
- Composer scripts as the canonical verification interface.

## Default decision

Laravel-native-first. Do not add a package, service, client, queue, authentication mechanism or persistence abstraction when the framework or an approved capability owner already satisfies the requirement.


## Canonical verification baseline

- PHP 8.5
- Laravel 13
- PostgreSQL major 18; canonical container reference is PostgreSQL 18.4
- Redis
- Node 22
- Composer/npm dependencies restored from repository lockfiles
- Docker Desktop + WSL2 is the primary development and verification runtime; Laragon is compatibility-only

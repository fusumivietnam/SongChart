# Technology Stack

## Runtime

- PHP 8.4 as the project baseline.
- Laravel 13.
- Composer 2.
- Node.js current LTS for asset tooling.
- Vite.

## Application

- Blade for page composition.
- Livewire 4 for interactive server-driven components.
- Alpine.js for small client-side behavior.
- Tailwind CSS 4.2 with semantic design tokens.
- Laravel Fortify for authentication backend.
- Laravel Socialite for approved OAuth providers.
- Laravel Sanctum only when a first-party API is actually required.

## Data

- PostgreSQL 18.x; major 18 is the release-authoritative database baseline.
- Redis for queue/cache in production; database queue may be used locally initially.
- Laravel Scout abstraction.
- Meilisearch introduced only after database search becomes a measured bottleneck.
- Object storage only for first-party images/files.

## Quality

- Pest.
- Laravel Pint.
- Larastan/PHPStan.
- Rector for controlled upgrades.
- GitHub Actions.
- Conventional Commits.
- Dependabot or Renovate.

## Observability

- Structured application logs.
- Laravel Pulse for application-level visibility where appropriate.
- Error tracking service selected before production.
- Provider request metrics: latency, status, quota and error category.

## Local development

Laragon:
- Apache or Nginx.
- PHP 8.4.
- PostgreSQL.
- Redis optional.
- Mailpit.
- HTTPS local domain preferred.

Docker is optional and must not become a prerequisite during initial development.

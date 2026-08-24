# Stage 16.4 — Laravel Pulse Operational Observability

## Authority and official sources

### Repository authorities

Stage 16.3 queue infrastructure, PostgreSQL schema ownership, `AppServiceProvider` operations authorization, performance baseline rules and the business-only admin information architecture remain authoritative.

### Installed versions

PHP 8.5+ / Laravel 13 / PostgreSQL 17+ remain the SongChart baseline. Stage 16.4 requires `laravel/pulse:^1.7.4`.

### Official external sources

- Laravel 13 Pulse documentation: installation, PostgreSQL storage, `/pulse` dashboard, `viewPulse` Gate, recorders, dedicated database, Redis ingest, sampling/trimming, `pulse:check` and `pulse:work`.
- Official laravel/pulse v1.7.4 Composer metadata: Laravel 13 support and PHP ^8.1 compatibility.
- Laravel Pulse security advisory GHSA-8vwh-pr89-4mw2: versions before 1.3.1 affected; v1.7.4 is above the patched floor.

### Native capability assessment

Pulse already records Laravel requests, jobs, queues, cache interactions, exceptions, slow queries and Laravel HTTP-client calls. SongChart should configure and authorize this capability rather than implement another technical monitoring dashboard.

### Custom implementation justification

Custom SongChart code is limited to thresholds, authorization mapping, schema/impact authority and deployment documentation. Domain/Application code does not import Pulse or emit custom Pulse metrics.

## Objective

Adopt Laravel Pulse as the first-party operational observability surface while keeping business administration and product analytics separate.

## Scope

- require laravel/pulse ^1.7.4
- publish/configure Pulse recorder configuration
- PostgreSQL Pulse tables and schema authority
- `/pulse` authorization through `viewPulse` -> `canViewOperations()`
- slow query/request/job/outgoing HTTP thresholds
- storage ingest locally; documented dedicated DB/Redis ingest production profile
- tests, verifier, impact-map and docs

## Non-goals

- custom Pulse cards or custom `Pulse::record` metrics
- business analytics
- queue supervision (Stage 16.3/Horizon)
- Nightwatch/Telescope
- exposing Pulse inside `/admin`

## Expected files

`composer.json`, `config/pulse.php`, Pulse migration, `.env.example`, `AppServiceProvider`, Pulse docs/tests/verifier, schema ownership and impact map.

## Allowed incidental files

README, documentation index/history, Composer scripts and legacy Stage 16.4 governance preservation.

## Scope deviations

The upstream Pulse migration is stored with a SongChart timestamp/name while preserving upstream v1.7.4 table definitions, so schema history remains deterministic in this repository.

## Tests and verification

Run PHP syntax, Pulse verifier, repository/docs/schema/impact/Laravel alignment/static governance. On dependency-installed target run Composer update for Pulse, package discovery, Pint, Larastan, focused Pest, PostgreSQL migration and `/pulse` authorization smoke.

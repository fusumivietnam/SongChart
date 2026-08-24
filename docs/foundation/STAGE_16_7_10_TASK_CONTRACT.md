# Stage 16.7.10 — Performance, Query & Runtime Reliability Baseline

## Objective

Close the performance/reliability gap before provider mutation work by making query budgets, bounded admin reads, strict Eloquent behavior, and database-environment readiness executable release invariants.

## Authority and official sources

### Repository authorities

- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/performance/performance-contracts.json`
- `app/Providers/AppServiceProvider.php`
- `composer.json`

### Installed versions

Installed dependency versions are authoritative from target-machine lockfiles. This stage does not change dependency versions.

### Official external sources

Laravel framework behavior and PostgreSQL/PDO behavior remain governed by the installed framework/runtime. No new external integration is introduced.

### Native capability assessment

Use native Eloquent strict mode, explicit selects/eager loads, pagination/limits, PDO connectivity checks, and existing Composer/Pest/PHPStan/Pint gates.

### Custom implementation justification

SongChart-specific query budgets and hot-path invariants require repository-local machine-readable contracts and verification because framework defaults cannot know use-case budgets or operational table ownership.

## Use-case and data surface

Read-only admin use cases covered: providers, imports, quarantine, identity conflicts. No product mutation or provider API call is introduced.

## Expected files

- `docs/project/performance/performance-contracts.json`
- `scripts/verify-performance-baseline.php`
- `scripts/verify-runtime-environment.php`
- `tests/Feature/AdminReadModelQueryBudgetTest.php`
- `app/Support/Admin/ProviderOperationsConsole.php`
- `composer.json`

## Allowed incidental files

- README/current-stage metadata
- documentation index/history/roadmap
- target Pint formatting on changed PHP files

## Scope deviations

None intended. Database indexes are not added without query-plan evidence from a representative PostgreSQL dataset.

## Tests and verification

- `composer performance:verify`
- `composer environment:verify`
- `php artisan test tests/Feature/AdminReadModelQueryBudgetTest.php`
- existing Provider Operations and Identity Conflict tests
- `composer release:verify`

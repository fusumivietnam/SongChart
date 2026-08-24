# Stage 16.5.1 — Repository Contract, Runtime & Release Safety Closure

## Objective

Convert recurring historical SongChart failures into permanent machine-enforced repository, dependency, runtime, database, test-isolation and release-safety contracts before feature development continues.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- package registry and candidate verification contract
- schema ownership
- PostgreSQL-only release authority
- reproducible Docker verification
- Laravel-native alignment
- Stage 16.5 privileged audit closure and corrective history

### Installed versions

- PHP 8.5 release authority
- Laravel 13
- PostgreSQL 18
- Node 22 canonical container toolchain
- `spatie/laravel-activitylog:^5.0`

### Official external sources

- Laravel 13 `RefreshDatabase` owns conventional test database refresh/transaction lifecycle.
- Composer lockfiles provide exact dependency versions for reproducible `install`.
- Spatie Activitylog publishes package migrations and explicitly requires ID-column adaptation when non-integer identifiers are used.
- Docker Compose service health dependencies remain the canonical test infrastructure boundary.


### Native capability assessment

Laravel `RefreshDatabase` remains the native database test-isolation mechanism. Composer lockfiles remain the dependency reproducibility authority. Docker Compose remains the service orchestration authority. PostgreSQL `information_schema` is used for runtime schema verification instead of a custom schema database.

### Custom implementation justification

Custom SongChart code is limited to project-specific contracts and verifiers: mapping historical regressions, documenting intentional package adaptations, checking critical model/schema fields, enforcing release orchestration rules, and asserting PostgreSQL runtime schema. These do not replace Laravel, Composer, Docker, PostgreSQL or package-native capabilities.

## Scope

- historical regression ledger with permanent guards
- package-owned schema contract registry
- critical model/database contract registry
- runtime environment and PHP-extension contract registry
- installer/release orchestration contract
- database Feature-test isolation verifier
- release lockfile authority
- static package/model/runtime contract gates
- runtime PostgreSQL migration contract verification
- canonical full PostgreSQL suite starts from a fresh isolated schema
- architecture/runtime contract tests
- authority dependency, impact-map and release-pipeline integration

## Non-goals

- authorization feature work (Stage 16.6)
- adding application features
- changing provider/discovery business semantics
- weakening PHPStan/Larastan or test coverage
- introducing SQLite as a release lane
- resolving dependencies on the target during normal release apply
- replacing Laravel/Composer/Docker native capabilities with custom equivalents

## Contract principles

1. Evidence before implementation: repository model/migration/package source must be inspected before field or schema assumptions.
2. A regression closes only with a permanent guard.
3. Package-owned tables must preserve upstream schema except documented adaptations.
4. Database Feature tests must declare Laravel-native isolation.
5. Focused tests and full canonical tests may not share unbounded dirty state.
6. Release candidates require dependency lockfiles; canonical verification installs, never updates, dependencies.
7. Runtime profiles and PHP extensions are machine-readable authorities.
8. Static contracts complement, but do not replace, real PostgreSQL runtime assertions.

## Expected files

Machine-readable authorities, static verifiers, architecture/Feature contract tests, Composer/canonical pipeline changes, stage documentation and governance updates.

## Validation

Packaging can validate syntax and static repository contracts. Because the historical full-source artifact does not include `composer.lock` or `package-lock.json`, strict release lockfile closure must occur on the authoritative target repository. The target must then pass full canonical verification before Stage 16.5.1 is closed.


## Tests and verification

Packaging runs PHP syntax and static contract/governance verifiers. Target verification must additionally run locked Composer/npm installs, Pint, PHPStan/Larastan, PostgreSQL 18 clean-schema tests, runtime migration contracts, frontend build and canonical evidence recording. Strict release lockfile verification is target-only for this changeset because the historical v18.5 full-source artifact omitted both lockfiles.

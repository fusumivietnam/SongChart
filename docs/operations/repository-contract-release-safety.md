# Repository Contract, Runtime & Release Safety

## Purpose

This authority exists to prevent recurring debug loops where source compiles locally but code, package schema, PostgreSQL state, test isolation, runtime or installer assumptions diverge.

## Contract families

### Regression contract

Every historical failure class is recorded in `docs/project/engineering/regression-ledger.json`. A regression is closed only when a permanent machine guard exists.

### Package schema contract

Package-owned database surfaces are recorded in `docs/project/stack/package-schema-contracts.json`. SongChart must preserve upstream columns and document only intentional adaptations.

For `spatie/laravel-activitylog:^5.0`, SongChart adapts `subject_id` and `causer_id` to ULID `char(26)` while preserving package-owned JSON columns including `attribute_changes` and `properties`.

### Model/schema contract

Critical application models are mapped to required database columns and casts. This protects field names used in privileged/auth/provider paths.

### Database test isolation

Database-writing Feature tests must use a Laravel database-isolation concern such as `RefreshDatabase`. Raw orchestration must not invoke database Feature tests against an ambient `.env`.

### Clean full-suite boundary

`test:postgres` and `test:feature` run through:

```text
scripts/run-database-tests.php postgres --prepare-schema
```

The runner verifies test-database safety, performs `migrate:fresh` only on the isolated test database, re-verifies the marker, then runs the requested suite.

### Runtime contract

`runtime-environments.json` distinguishes Laragon development, Docker development and canonical test profiles. It also records which PHP extensions come from the base image, are compiled, or are installed through PECL.

### Lockfile authority

Release mode requires both:

```text
composer.lock
package-lock.json
```

Canonical verification must use:

```text
composer install
npm ci
```

and never dependency `update`.

### Runtime migration contract

After migrations and the full PostgreSQL suite, `verify-migration-runtime-contract.php` checks actual `information_schema` state for package-owned columns, types, nullability and identifier lengths.

## Normal stage workflow

```text
read authorities
→ implement
→ repository-contracts:verify
→ Pint
→ PHPStan/Larastan
→ clean PostgreSQL schema
→ focused tests
→ clean full PostgreSQL suite
→ runtime migration contract
→ frontend build
→ canonical evidence
```

The canonical lane remains the final closure authority, but it should confirm known contracts rather than be the first place schema/runtime drift is discovered.


## Single database-test alias authority

Composer PostgreSQL test aliases are owned by:

```text
docs/project/stack/database-test-contract.json
```

`verify-database-authority.php` and `verify-runtime-authority-closure.php` must consume this authority instead of embedding duplicate expected command strings. This prevents verifier drift when the test lane adds safety behavior such as `--prepare-schema`.


## Failure evidence preservation

PostgreSQL test failures must retain the exact Laravel/Pest evidence. The database runner streams test output and also writes a redacted failure capture to:

```text
storage/logs/postgres-test-last-failure.log
```

The final 160 lines are repeated immediately at failure so Composer/PowerShell wrapper messages cannot hide the original assertion or SQLSTATE. Application/schema fixes must be based on that evidence rather than inferred from wrapper exit codes.

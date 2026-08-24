# Stage 16.5.1 Validation Report

Status: implementation candidate v19; target canonical closure pending.

## Implemented

- `regression-ledger.json` maps historical failure classes to permanent guards.
- package schema contract catches upstream/adapted migration drift.
- model/schema contracts catch critical field/cast drift such as `Extension.active_version`.
- database Feature-test isolation is enforced repository-wide by static scan.
- runtime environment contract pins canonical PostgreSQL/PHP-extension responsibilities.
- release orchestration rejects `php composer.phar`, ambient database Feature tests, and canonical dependency updates.
- full PostgreSQL test aliases now opt into `--prepare-schema`, creating a clean schema before the suite.
- release lockfile authority is strict in release mode.
- PostgreSQL migration runtime contract checks package-owned column presence, type, nullability and ULID length.
- architecture and PostgreSQL Feature tests exercise the contract closure.

## Packaging environment findings

The historical v18.5 full-source candidate used as the packaging baseline does not contain:

- `composer.lock`
- `package-lock.json`

This is now classified as `REG-011 lockfile-reproducibility`.

The new lockfile verifier therefore:
- reports this condition in non-release/static packaging mode;
- fails in `--release` mode.

The v19 changeset is intended for the authoritative target repository that already produced the Stage 16.5 canonical PASS and therefore owns the current resolved dependency graph. The installer refuses to proceed if either lockfile is absent.

## Not claimed in packaging environment

- Composer 2 dependency install under PHP 8.5
- PostgreSQL 18 runtime contract test
- Pint
- PHPStan/Larastan
- full Pest suite
- npm build
- canonical closure

These remain target gates and must not be inferred from static packaging checks.


## v19.1 corrective — shared database-test authority

The first v19 target quality run failed in `verify-runtime-authority-closure.php`. Stage 16.5.1 had deliberately changed `test:feature` to add `--prepare-schema`, and `verify-database-authority.php` was updated, but `verify-runtime-authority-closure.php` still embedded the previous alias.

v19.1 removes this duplicated authority. `docs/project/stack/database-test-contract.json` now owns the canonical Composer aliases for `test`, `test:all`, `test:feature`, and `test:postgres`. Both database and runtime closure verifiers consume that one contract.

Regression `REG-013 verifier-authority-drift` permanently records this failure class. Architecture governance checks that Composer and both verifiers remain tied to the shared contract.


## v19.2 corrective — shared release pipeline ordering authority

The v19.1 target run reached `performance:verify`. The performance verifier still assumed `@environment:verify` must be `release:verify[0]`. Stage 16.5.1 intentionally places the static `@lockfile-authority:release` gate before environment preflight, so this positional assertion was obsolete.

v19.2 introduces `docs/project/stack/release-pipeline-contract.json`. The shared contract defines preflight order and the stronger invariant: environment verification must occur before runtime/database-dependent release steps, while a static lockfile gate may run before it.

Both `verify-performance-baseline.php` and `verify-release-orchestration.php` consume this contract. Regression `REG-014 release-pipeline-order-verifier-drift` permanently records the failure class.


## v19.3 corrective — exact PostgreSQL failure evidence preservation

The v19.2 canonical run reached the full PostgreSQL suite, but the supplied log excerpt contained only Composer/PowerShell wrappers and not the failing Pest assertion or SQLSTATE. Changing application or database code without that evidence would violate Stage 16.5.1's evidence-before-implementation rule.

v19.3 hardens `scripts/run-database-tests.php` so test output is streamed live and captured simultaneously. On failure it:

- persists the captured Laravel/Pest output at `storage/logs/postgres-test-last-failure.log`;
- redacts password-like environment values;
- prints the last 160 plain-text lines immediately before the database diagnostic and Composer wrapper;
- retains the existing PostgreSQL host/database/version/migration/environment-marker diagnostics.

Regression `REG-015 test-failure-evidence-loss` permanently records this diagnostic failure class. v19.3 intentionally does not guess or change application behavior without the missing failing-test evidence.


## v19.4 corrective — architecture test consumes shared database authority

The v19.3 canonical evidence showed PostgreSQL 18.4, 17 migrations and the `testing/testing` environment marker were correct. The sole failure was `PostgresFirstDatabaseAuthorityTest`, which still asserted the old literal `@php scripts/run-database-tests.php postgres`.

v19.4 makes that architecture test consume `docs/project/stack/database-test-contract.json`, the same authority already used by the database and runtime verifiers. It also explicitly asserts that both PostgreSQL Feature and full-suite aliases retain `--prepare-schema`.

Regression `REG-016 architecture-test-authority-drift` records this failure class so architecture tests may not independently duplicate runtime authority values.

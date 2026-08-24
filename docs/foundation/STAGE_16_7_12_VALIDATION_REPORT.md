# Stage 16.7.12 Validation Report

Status: packaging-time validation record; destructive PostgreSQL target-machine tests are not claimed.

## Packaging validation
- PHP syntax is checked for the safety policy, migration, verifier, middleware, command, and regression tests.
- local data-safety static verifier passes.
- Composer JSON and PHPUnit XML remain parseable.
- repository documentation/governance verifiers are expected to run before packaging closure.

## Safety invariants
- PostgreSQL test runner no longer falls back from `TEST_PGSQL_DATABASE` to development `DB_DATABASE`.
- destructive PostgreSQL tests are refused when development and test database names match.
- test database name must end with `_test`.
- database marker must be `database_role=testing` after the marker migration is present.
- local 2FA bypass is available only when application environment is exactly `local`.
- `admin:ensure-local` updates an existing account in place and preserves password/two-factor state.

## Target-machine evidence still required
- create/confirm dedicated `songchart_test` PostgreSQL database
- target Pint
- Larastan/PHPStan
- SQLite/PostgreSQL Pest lanes
- marker migration on both development and dedicated test databases
- `composer test-database:safety`
- `composer release:verify`

No claim is made that target PostgreSQL tests passed until Laragon executes the guarded release lane successfully.

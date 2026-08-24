# Test database setup

SongChart uses PostgreSQL as the only release-authoritative database test engine. `phpunit.xml` does not own the database connection.

## PostgreSQL authority

```bash
composer test
composer test:postgres
composer test:feature
```

These commands use `TEST_PGSQL_DATABASE` (default `songchart_test`). The test database never falls back to development `DB_DATABASE`. Its name must end in `_test`, must differ from the development database, and its `songchart_environment_guard` row must declare `database_role=testing` before destructive tests can run.

Recommended local values:

```env
DB_DATABASE=songchart
DB_USERNAME=songchart_admin
TEST_PGSQL_DATABASE=songchart_test
TEST_PGSQL_USERNAME=songchart_admin
```

Run `composer test-database:safety` before investigating PostgreSQL test failures. A refusal is a safety outcome; do not bypass it by pointing tests at the development database.

## SQLite compatibility lane

```bash
composer test:sqlite:compat
```

SQLite is retained temporarily as an optional compatibility/speed diagnostic only. It is not part of `composer test`, CI release authority, or `composer release:verify`, and a SQLite pass must never be used to claim PostgreSQL correctness. New database-dependent behavior must be designed and verified against PostgreSQL semantics.

## Suite classification

- `tests/Unit`: database-free by default.
- `tests/Architecture`: database-free by default.
- `tests/Feature`: PostgreSQL-backed when application data is involved.
- PostgreSQL-specific performance, locking and query-plan behavior must not be approximated with SQLite.

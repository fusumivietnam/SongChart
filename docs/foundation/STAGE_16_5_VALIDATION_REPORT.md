# Stage 16.5 Validation Report

Status: implementation candidate v18; target Composer resolution/database/canonical closure pending.

Implemented:
- Spatie Activitylog v5 package admission
- explicit audit adapter and privacy policy
- ULID-aware activity table
- provider/import/identity/extension privileged audit events
- audited user role and activation commands
- last-active-super-admin safeguards
- paginated admin audit viewer protected by operations capability
- schema/package/impact governance
- architecture/feature tests and static verifier

Packaging environment limitation:
- no target Composer lockfile/vendor dependency graph
- no target PostgreSQL runtime
- no canonical Docker runtime

The changeset installer therefore resolves `spatie/laravel-activitylog:^5.0` on the target before repository quality gates. Closure must be established by the target canonical lane.


## v18.1 corrective — target PHPStan/Larastan closure

The first target quality run exposed six strict static-analysis errors:

- four invalid `Extension::$version` reads; the canonical Extension attribute is `active_version`;
- one missing iterable value type on privileged-audit filters;
- one unnecessary Collection `count()` call after locking active super-admin rows.

v18.1 uses `active_version`, declares `array<string, mixed>`, and preserves the `SELECT ... FOR UPDATE` safety read while counting the already-locked result with PHP `count()`.

No PHPStan baseline or rule weakening is introduced.


## v18.2 corrective — database-backed focused tests use canonical PostgreSQL authority

The v18.1 target run passed static analysis, then the installer invoked `php artisan test` directly for `PrivilegedAuditTest`. Laravel therefore inherited the ambient local `.env` and attempted to connect to `127.0.0.1:5432 / songchart`. The local PostgreSQL process was not running, so both tests failed before application assertions executed.

v18.2 removes ambient-host database dependence from the Stage 16.5 focused-test workflow. Database-backed focused tests now run inside `compose.verify.yml` and through `scripts/run-database-tests.php postgres`, which supplies `songchart_verify_test` and the existing test-database safety policy.

This is a test-environment correction. No application-domain behavior is weakened and no SQLite fallback is introduced.


## v18.3 corrective — isolated PostgreSQL schema preparation

The v18.2 focused lane reached the correct canonical database (`songchart_verify_test`) but the database was empty. SongChart Feature tests do not use Laravel's `RefreshDatabase` trait globally, so no migration was executed before the first factory insert.

v18.3 makes schema lifecycle explicit in `scripts/run-database-tests.php` through `--prepare-schema`. The runner:

1. resolves the PostgreSQL test environment;
2. runs the existing `TestDatabaseSafetyPolicy`;
3. executes `php artisan migrate:fresh --force` only against that isolated test database;
4. re-runs the database safety guard after migrations establish the environment marker;
5. executes the requested focused tests.

The standard database runner behavior is unchanged unless `--prepare-schema` is explicitly requested. No development database is used or dropped.


## v18.4 corrective — Spatie Activitylog v5 schema alignment

The v18.3 target run prepared the isolated PostgreSQL schema successfully and reached the audit write. Spatie Activitylog v5 then attempted to persist the `attribute_changes` column, but the SongChart-adapted migration had omitted that upstream v5 column.

The official Spatie v5 migration defines both `attribute_changes` and `properties` as nullable JSON columns. v18.4 restores `attribute_changes` while retaining SongChart's ULID-compatible `subject_id` and `causer_id` fields.

The privileged-audit verifier and architecture test now require the upstream v5 column so package/schema drift is caught statically before a database write.


## v18.5 corrective — focused Feature test isolation

The v18.4 focused audit test passed far enough to leave its fixture rows in `songchart_verify_test`. `PrivilegedAuditTest` did not use Laravel `RefreshDatabase`, so the enabled `audit-fixture` provider survived into the subsequent full canonical suite.

That contamination explains both downstream provider-health failures:

- the health dispatcher found the test's enabled `audit-fixture`, producing 2 dispatches instead of 1;
- the deduplication test skipped its own recent provider but still dispatched the leftover `audit-fixture`, producing 1 instead of 0.

v18.5 applies Laravel's native `RefreshDatabase` concern to `PrivilegedAuditTest`. Production provider-health behavior is unchanged. Static/architecture governance now requires this isolation for the focused audit Feature test.

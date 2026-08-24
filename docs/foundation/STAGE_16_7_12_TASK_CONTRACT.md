# Stage 16.7.12 — Local Data Safety & Development Auth Ergonomics

## Authority and official sources
### Repository authorities
- `scripts/run-database-tests.php`
- `scripts/verify-runtime-environment.php`
- `app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php`
- `app/Console/Commands/SetupLocalCommand.php`
- `app/Console/Commands/CreateAdminCommand.php`
- `config/songchart.php`
- `.env.example`
- `phpunit.xml`

### Installed versions
Committed Composer constraints and target-machine lockfiles remain the installed-version authority.

### Official external sources
Laravel testing/database migration behavior, environment configuration, Fortify two-factor authentication, and Artisan command behavior are the applicable framework-native references. No third-party package is introduced by this stage.

### Native capability assessment
Laravel already provides isolated testing environments, database migrations, Eloquent test traits, middleware, configuration, and Artisan commands. SongChart only needs project-specific safety policy around which PostgreSQL database destructive tests are allowed to target.

### Custom implementation justification
The existing PostgreSQL test runner could fall back from `TEST_PGSQL_DATABASE` to the development `DB_DATABASE`. Because feature tests use destructive database-refresh behavior, SongChart requires a fail-closed test-database identity guard before any PostgreSQL test process starts. Local 2FA ergonomics also need a local-only bypass that cannot weaken staging/production enforcement.

## Use case
Protect local development data from destructive test/reset behavior while preserving convenient local administration without repeatedly reconfiguring two-factor authentication.

## Exact reads
- local `.env` development database name and PostgreSQL connection credentials
- `TEST_PGSQL_*` dedicated test connection settings
- `songchart_environment_guard` marker in the target PostgreSQL test database
- existing local user by email for idempotent administrator bootstrap
- `SONGCHART_ADMIN_2FA_MODE` for local-only middleware behavior

## Exact writes
- additive migration creates `songchart_environment_guard`
- `admin:ensure-local` may set an existing local user's role, `is_active`, and missing `email_verified_at`
- `admin:ensure-local` must not change an existing password, TOTP secret, recovery codes, or two-factor confirmation timestamp

## Null and identifier semantics
- test PostgreSQL database name must be non-empty, differ from the development database, and end in `_test`
- marker row uses singleton identifier `songchart`
- existing user identity is resolved by normalized email; the user record is updated in place, never deleted/recreated

## Routes
No route changes.

## Expected files
- `app/Support/Testing/TestDatabaseSafetyPolicy.php`
- `scripts/verify-test-database-safety.php`
- `scripts/verify-local-data-safety.php`
- `database/migrations/2026_08_09_000100_create_songchart_environment_guard_table.php`
- `app/Console/Commands/EnsureLocalAdminCommand.php`
- `tests/Unit/TestDatabaseSafetyPolicyTest.php`
- `tests/Feature/Development/LocalAdminErgonomicsTest.php`
- `tests/Architecture/LocalDataSafetyArchitectureTest.php`
- `.env.testing.example`

## Allowed incidental files
- `scripts/run-database-tests.php`
- `scripts/verify-runtime-environment.php`
- `app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php`
- `app/Console/Commands/SetupLocalCommand.php`
- `config/songchart.php`
- `.env.example`
- `phpunit.xml`
- `composer.json`
- repository stage/history/documentation metadata

## Scope deviations
None. The stage does not remove production/staging 2FA requirements and does not back up/restore development rows as a substitute for database isolation.

## Tests and verification
- `composer local-data-safety:verify`
- `composer test-database:safety`
- `php artisan test tests/Unit/TestDatabaseSafetyPolicyTest.php`
- `php artisan test tests/Architecture/LocalDataSafetyArchitectureTest.php`
- `php artisan test tests/Feature/Development/LocalAdminErgonomicsTest.php`
- `composer release:verify`

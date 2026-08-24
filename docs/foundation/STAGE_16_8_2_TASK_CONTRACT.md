# Stage 16.8.2 — Test Authority & Code Generation Guardrails

## Authority and official sources

### Repository authorities

`docs/setup/TEST_DATABASE_SETUP.md`, `docs/setup/FEATURE_TEST_ASSERTIONS.md`, `docs/project/docs/ENGINEERING_WORKFLOW.md`, `docs/project/stack/impact-test-map.json`, the repository quality/release Composer scripts, and Stage 16.7.12 database-safety contracts remain authoritative. This stage changes test authority and generation guardrails; it does not change product behavior or provider mutation semantics.

### Installed versions

Laravel 13 / PHP 8.3+ / Pest 4 / Larastan 3 repository baseline. PostgreSQL 17+ remains the production and integration database authority.

### Official external sources

- Laravel 13 database testing documentation for `RefreshDatabase`, database assertions, and application test execution.
- Laravel 13 testing documentation for test suite organization and parallel testing capabilities.
- PHPStan guidance for explicit iterable value types and precise static-analysis boundaries.

### Native capability assessment

Laravel/Pest already provide the required test runner, database refresh behavior, named test suites, and assertions. PostgreSQL-specific correctness does not require a second SQLite release lane. Repository scripts and static verifiers are sufficient to enforce code-generation conventions without adding another testing framework.

### Custom implementation justification

SongChart repeatedly encountered the same generated-code failures at Pest `$this`, Eloquent enum/datetime magic, broad UI text assertions, and stale legacy tests. A small repository verifier and mandatory generation rules are justified because these failures are repository-specific conventions spanning framework magic, current static-analysis settings, semantic UI selectors, impact maps, and changeset packaging.

## Changed authorities

- `database-test-authority`
- `admin-dashboard-mode`

## Scope

- Make PostgreSQL `songchart_test` the only release-authoritative database test lane.
- Route `composer test`, `test:all`, and `test:feature` through PostgreSQL safety/isolation.
- Remove SQLite from CI and `release:verify`; retain `test:sqlite:compat` temporarily as a non-authoritative compatibility diagnostic.
- Add stable `data-admin-nav` selectors and reconcile the Stage 16.8.1 role-aware navigation regression test.
- Add mandatory code-generation rules covering Pest TestCase context, Eloquent enum/datetime boundaries, iterable value types, dead-code cleanup, semantic UI assertions, framework magic, impact preflight, and packaging preflight.
- Add executable code-generation and authority-dependency closure verifiers plus architecture regression tests.
- Reconcile every current executable/test/document dependent of the PostgreSQL-only and attention-first authority changes in one repository-wide closure.
- Block deprecated authority tokens in current executable surfaces before packaging.
- Require current-stage changed Pest tests that use Laravel helpers through `$this` to declare `Tests\TestCase` explicitly.
- Require current-stage changed Pest tests to use static-analysis-safe negative assertions and avoid known-class `is_subclass_of()` tautologies.
- Update database/testing documentation, agent instructions, impact map, repository history, and current-stage authority.

## Non-goals

- No product feature changes.
- No provider mutation/recovery behavior changes.
- No schema or migration changes.
- No removal of SQLite runtime support from Laravel itself.
- No claim that PostgreSQL tests replace future browser, load, staging, or production tests.

## Expected files

- `composer.json`
- `.github/workflows/tests.yml`
- `resources/views/components/admin/sidebar.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `tests/Feature/AdminOperationsUxTest.php`
- `tests/Feature/AdminDashboardTest.php`
- `tests/Feature/ProviderOperationsConsoleTest.php`
- `tests/Architecture/PostgresFirstDatabaseAuthorityTest.php`
- `tests/Architecture/RepositoryRuntimeAuthorityClosureTest.php`
- `tests/Architecture/TestingAndCiGatesTest.php`
- `tests/Architecture/TestAuthorityCodeGenerationGuardrailsTest.php`
- `tests/Architecture/AuthorityDependencyClosureTest.php`
- `scripts/verify-database-authority.php`
- `scripts/verify-runtime-authority-closure.php`
- `scripts/verify-foundation-closure.php`
- `scripts/verify-code-generation-guardrails.php`
- `scripts/verify-authority-dependencies.php`
- `scripts/verify-admin-operations-ux.php`
- `docs/project/governance/authority-dependencies.json`
- `docs/foundation/CODE_GENERATION_RULES.md`
- `docs/setup/FEATURE_TEST_ASSERTIONS.md`
- `docs/setup/TEST_DATABASE_SETUP.md`
- `docs/project/docs/TESTING.md`
- `docs/project/stack/DATABASE_CONVENTIONS.md`
- `docs/project/stack/impact-test-map.json`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`
- `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`
- `docs/ui/PHASE_10_ADMIN_DASHBOARD.md`
- `docs/ui/PHASE_3_UI_PREVIEW.md`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
## Allowed incidental files

- `README.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_8_2_VALIDATION_REPORT.md`

## Scope deviations

None.

## Tests and verification

Run code-generation, database-authority, runtime-authority, documentation, repository-state, official-source, impact-map, type-guardrail, local-data-safety, PHP syntax, JSON and packaging verification. On the Laragon target, run Pint, focused Larastan/PHPStan for changed PHP/tests, focused PostgreSQL tests, the full PostgreSQL suite, frontend build, and `composer release:verify`. SQLite compatibility results must not be used for release claims.

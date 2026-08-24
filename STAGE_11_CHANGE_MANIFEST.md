# Stage 11 Change Manifest

Current delivery: `Stage 11.3 — Laravel Feature Alignment Audit`

## Stage 11.3 added

- `docs/foundation/STAGE_11_3_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_3_LARAVEL_FEATURE_ALIGNMENT_AUDIT.md`
- `scripts/verify-laravel-alignment.php`
- `tests/Architecture/LaravelFeatureAlignmentTest.php`

## Stage 11.3 changed

- `AGENTS.md`
- `README.md`
- `composer.json`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `STAGE_11_CHANGE_MANIFEST.md`

## Stage 11.3 removed

None.

## Stage 11.3 behavior

- records a source-backed Laravel 13/Fortify alignment matrix;
- distinguishes framework-native, accepted custom, deferred and remediation work;
- prevents raw cURL, application-level `env()`, custom admin middleware restoration, controller mail transport and extension inline validation;
- adds `composer alignment:verify` to `composer verify`;
- makes no route, schema, provider or runtime feature change.

## Previous Stage 11 history

See repository history and the Stage 11 implementation status for Stage 11, 11.2 and corrective packaging details.

## Stage 11.3.1 — Quality Gate Compatibility & Pint Baseline Normalization

Added:
- `docs/foundation/STAGE_11_3_1_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_3_1_QUALITY_GATE_BASELINE.md`
- `scripts/verify-source-package.php`

Modified:
- `phpstan.neon` removes the PHPStan 1.x-only option.
- `composer.json` adds normalization and consolidated quality verification.
- Documentation authorities record the quality baseline and packaging rules.

Removed by change-set:
- stray root `payload/` directory when present.

No runtime, route, schema or provider behavior changed.

## Larastan Symfony Console compatibility patch

- Renamed the custom `extension:rollback --version` option to `--release`. Symfony Console already owns the global `--version` option, and the duplicate name caused Larastan/PHPStan to fail while reflecting the command.
- Added `ConsoleCommandSignatureTest` to prevent the option collision from returning.

## Stage 11.4

Added enum-backed roles and granular extension authorization through Laravel Gates; updated routes, views, tests and foundation documentation.


## Stage 11.4.1 quality remediation

Larastan findings from Stage 11.4 verification were resolved through explicit iterable shapes, Eloquent relation generics, safe manifest narrowing, and command path-regex compatibility. No ignore baseline was introduced.

## Stage 11.4.2 — Larastan cast boundary normalization

Modified:

- `app/Extensions/ExtensionRemovalManager.php`
- `app/Extensions/ExtensionRuntimeRegistry.php`
- `app/Extensions/ExtensionUpgradeManager.php`
- `app/Support/Admin/AdminDashboardSnapshot.php`
- `docs/foundation/STAGE_11_4_2_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`

Purpose: resolve remaining PHPStan 2/Larastan cast-boundary findings without ignore rules or behavior changes.

## Stage 11.4.3 — Provider status cast inference hotfix

Modified:
- `app/Support/Admin/AdminDashboardSnapshot.php`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `STAGE_11_CHANGE_MANIFEST.md`

Reason:
- Resolve the final Larastan error caused by inconsistent static inference of the Eloquent enum cast for `Provider::status`.


## Stage 11.4.4 — Enum presentation and command regression hotfix

Modified:
- `app/Enums/UserRole.php`
- `resources/views/account/overview.blade.php`
- `tests/Feature/ConsoleCommandSignatureTest.php`
- `tests/Feature/AuthenticationAccountShellTest.php`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `STAGE_11_CHANGE_MANIFEST.md`

Added:
- `docs/foundation/STAGE_11_4_4_TASK_CONTRACT.md`

Reason:
- Prevent enum objects from being passed to Laravel string helpers in Blade.
- Preserve the absence of the Symfony global `--version` collision.

## Stage 11.5 — Request and Action Normalization

Added a public `SearchRequest`, search page Action, focused extension Actions, architecture guardrails and governing documentation. Existing routes, schema, provider contracts and authorization gates are unchanged.

## Stage 11.6 — Async and provider infrastructure

Added:

- `app/Actions/Providers/DispatchProviderHealthChecks.php`
- `app/Console/Commands/DispatchProviderHealthChecksCommand.php`
- `app/Jobs/Providers/CheckProviderHealth.php`
- `app/Support/Providers/ProviderAdapterRegistry.php`
- `docs/foundation/STAGE_11_6_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_6_ASYNC_PROVIDER_MODEL.md`
- `tests/Architecture/ProviderAsyncInfrastructureTest.php`
- `tests/Feature/Providers/ProviderAsyncInfrastructureTest.php`
- `tests/Unit/Providers/ProviderAdapterRegistryTest.php`

Modified:

- `.env.example`
- `AGENTS.md`
- `README.md`
- `app/Providers/AppServiceProvider.php`
- `config/songchart.php`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/START_HERE.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `routes/console.php`
- `scripts/verify-laravel-alignment.php`

No migration, schema, public route, live provider adapter or credential change.


## Stage 11.6.1 — Provider command type normalization

Modified:

- `app/Console/Commands/DispatchProviderHealthChecksCommand.php`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `STAGE_11_CHANGE_MANIFEST.md`

Added:

- `docs/foundation/STAGE_11_6_1_TASK_CONTRACT.md`

Reason:

- Remove a redundant runtime type check that PHPStan/Larastan correctly identified as always true for Symfony Console array options.

## Stage 11.7 — Testing and CI Gates

### Added

- `scripts/verify-ci-configuration.php`
- `tests/Architecture/TestingAndCiGatesTest.php`
- `docs/foundation/STAGE_11_7_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_7_TESTING_CI_MODEL.md`

### Modified

- `.github/workflows/tests.yml`
- `phpunit.xml`
- `composer.json`
- `AGENTS.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/docs/TESTING.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`

### Removed

- None.

### Runtime impact

- No schema, route, provider, authentication or authorization behavior change.
- Architecture tests now actually execute as part of the declared test suites.
- CI now separates quality, SQLite, PostgreSQL and frontend failures.

## Stage 11.7.1 — Verification Chain Regression Hotfix

### Added

- `docs/foundation/STAGE_11_7_1_TASK_CONTRACT.md`

### Modified

- `tests/Architecture/DocumentationGovernanceTest.php`
- `tests/Architecture/LaravelFeatureAlignmentTest.php`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `STAGE_11_CHANGE_MANIFEST.md`

### Reason

Stage 11.7 moved documentation and Laravel-alignment verification beneath `quality:verify`. The older architecture tests still required those commands to be duplicated directly in `verify`, causing false failures even though the guardrails remained executable.

## Stage 11.8 — Documentation Reconciliation

### Added

- `docs/foundation/STAGE_11_8_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_FOUNDATION_BASELINE.md`
- `tests/Architecture/DocumentationReconciliationTest.php`

### Modified

- `README.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/TESTING.md`
- `scripts/verify-documentation.php`
- `STAGE_11_CHANGE_MANIFEST.md`

### Removed

None.

### Reconciliation result

- one unambiguous current development marker remains in `README.md`;
- Stage 11 current state is separated from historical patch records;
- one foundation baseline links to existing authorities instead of duplicating them;
- documentation verification prevents duplicate current-stage headings from returning;
- no runtime, schema, route, provider, authentication or authorization behavior changed.

### Stage 11.8 corrected v2

- Normalized `scripts/verify-documentation.php` to satisfy Laravel Pint.
- No runtime behavior or governance rule changed.


## Stage 11.9 — Foundation Closure Audit

Added:

- `scripts/verify-foundation-closure.php`
- `tests/Architecture/FoundationClosureAuditTest.php`
- `docs/foundation/STAGE_11_9_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_9_FOUNDATION_CLOSURE_AUDIT.md`

Updated:

- `AGENTS.md`
- `README.md`
- `composer.json`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_FOUNDATION_BASELINE.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/TESTING.md`

No runtime, schema, route, provider, authentication or authorization behavior changed.

## Stage 11.10 — Foundation Release Closure

### Added

- `scripts/verify-release-locks.php`
- `tests/Architecture/FoundationReleaseClosureTest.php`
- `docs/foundation/STAGE_11_10_TASK_CONTRACT.md`
- `docs/foundation/STAGE_11_10_FOUNDATION_RELEASE_CLOSURE.md`

### Modified

- `.github/workflows/tests.yml`
- `AGENTS.md`
- `README.md`
- `composer.json`
- `scripts/verify-ci-configuration.php`
- `scripts/verify-foundation-closure.php`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/foundation/STAGE_11_FOUNDATION_BASELINE.md`
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/TESTING.md`

### Target-machine generated

- `composer.lock`
- `package-lock.json`

### Runtime impact

No application behavior, schema, route, provider, authentication or authorization behavior changed. CI becomes strict about lockfiles and deterministic dependency installation.

Stage 11.10 also adds:

- `scripts/finalize-stage-11-release.ps1`
- `scripts/finalize-stage-11-release.bat`

These scripts generate both lockfiles on the target machine, reinstall from the resolved dependency graph and execute the strict release gate.

- NPM 12 remote tarball compatibility: the release finalizer sets `npm_config_allow_remote=all` only for its child npm commands and restores the previous environment value afterward.

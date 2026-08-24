# Stage 16.5.2 — Executable Repository Authority & Contract Compiler

## Objective

Replace duplicated repository authority literals with one executable resolver/graph so authority changes are resolved once, affected consumers are discoverable before implementation, canonical evidence fingerprints the exact verified tree, and source packaging is refused unless it matches that canonical evidence.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/domain/model-schema-contracts.json`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/domain/model-schema-contracts.json`
- Stage 16.5.1 regression ledger and release-safety closure

### Installed versions

- PHP 8.5 release authority
- Laravel 13
- PostgreSQL 18
- Node 22 canonical container
- Composer 2 locked dependency workflow
- `spatie/laravel-activitylog:^5.0`

### Official external sources

- PHP native JSON/hash/filesystem/process capabilities are sufficient for the compiler; no new package is required.
- Composer `install` from `composer.lock` remains the dependency reproducibility authority.
- Laravel Artisan remains the native CLI surface for `songchart:doctor`.
- PostgreSQL `information_schema` / `pg_indexes` remain the runtime schema inspection authority.

### Native capability assessment

No third-party dependency is needed. The implementation uses PHP deterministic hashing and filesystem traversal, Laravel's existing command infrastructure, Composer scripts as execution adapters, and PostgreSQL catalog inspection. The contract compiler does not replace Composer, Laravel or PostgreSQL; it resolves SongChart-specific repository authorities into one graph.

### Custom implementation justification

SongChart-specific custom code is justified for:
- resolving project authority files into deterministic fingerprints;
- mapping authorities to their registered consumers;
- detecting forbidden raw authority literals;
- resolving impacted authorities from changed paths;
- recording exact source-tree and lockfile fingerprints in canonical evidence;
- refusing source packaging when current tree provenance differs from canonical evidence.

These are project governance concerns not provided by Laravel or Composer.

## Changed authorities

- `executable-repository-authority`

## Scope

- executable `RepositoryContractResolver`
- declarative compiler registry
- compiled authority manifest/fingerprint
- no-raw-authority-literal enforcement
- semantic Composer consumer verification
- impact resolver
- `songchart:doctor --contract`
- PostgreSQL schema snapshot generator
- installed-package upstream adaptation snapshot
- exact canonical source-tree provenance
- post-canonical verified-source packager
- canonical evidence fingerprints
- architecture/governance integration

## Non-goals

- application authorization changes
- production data migration
- package upgrades
- replacing Laravel/Composer/PostgreSQL native functionality
- automatically modifying consumer source files
- claiming canonical closure in the packaging environment

## Expected files

- `app/Support/Engineering/RepositoryContractResolver.php`
- `tests/Unit/LaravelMigrationColumnExtractorTest.php`
- `app/Support/Engineering/LaravelMigrationColumnExtractor.php`
- `app/Console/Commands/SongChartDoctorCommand.php`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/generated/repository-contract-manifest.json`
- `scripts/compile-repository-contracts.php`
- `scripts/verify-repository-contract-compiler.php`
- `scripts/resolve-repository-impact.php`
- `scripts/snapshot-postgres-schema.php`
- `scripts/verify-package-upstream-adaptations.php`
- `scripts/verify-artifact-provenance.php`
- `scripts/package-verified-source.php`
- `scripts/record-canonical-verification.php`
- `scripts/verify-database-authority.php`
- `scripts/verify-runtime-authority-closure.php`
- `scripts/verify-performance-baseline.php`
- `scripts/verify-release-orchestration.php`
- `tests/Architecture/ExecutableRepositoryAuthorityTest.php`
- `tests/Architecture/PostgresFirstDatabaseAuthorityTest.php`
- `tests/Architecture/RepositoryContractSafetyClosureTest.php`
- `composer.json`
- `candidate-verification.json`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/domain/model-schema-contracts.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/stack/package-registry.json`
- `docs/foundation/STAGE_16_5_2_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_5_2_VALIDATION_REPORT.md`
- `docs/operations/executable-repository-authority.md`
- `README.md`
- `PROJECT_AUTHORITY.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`

## Allowed incidental files

- `docs/project/engineering/regression-ledger.json` if a new regression class is observed during target closure.

## Scope deviations

None planned.

## Tests and verification

Packaging must run PHP syntax and all static authority/governance checks that do not require installed dependencies. Target closure must run Composer install from lockfile, Pint, PHPStan/Larastan, architecture tests, installed-package upstream verification, clean PostgreSQL 18 full suite, PostgreSQL schema snapshot, frontend build, canonical evidence recording and candidate-contract recheck.

Final release-source packaging is intentionally post-canonical: `composer release:package` must refuse a tree until `candidate-verification.json` is `closure_ready=true` and its recorded graph/source/lock fingerprints still match the current tree.

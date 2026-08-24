# Stage 11.7 Testing and CI Model

## Gate order

The authoritative local release gate is `composer verify`:

1. clear Laravel optimization caches;
2. prove application routes can be discovered;
3. verify documentation, Laravel alignment, working-tree hygiene and CI configuration;
4. run Pint in check mode;
5. run Larastan;
6. run Unit, Architecture and Feature suites explicitly;
7. build production frontend assets.

## Test suites

- **Unit**: isolated contracts and domain helpers.
- **Architecture**: repository boundaries and regression guardrails. This suite must remain registered in `phpunit.xml`; merely storing tests under `tests/Architecture` is insufficient.
- **Feature**: HTTP, authentication, authorization, database, queue and application behavior.

Commands:

```bash
composer test:unit
composer test:architecture
composer test:feature
composer test:all
```

## CI jobs

### PHP quality gates

Runs documentation verification, Laravel alignment, source hygiene, CI configuration verification, Pint and Larastan.

### SQLite compatibility

Runs all suites on PHP 8.3 and PHP 8.4 using SQLite in memory. This is the fast compatibility signal.

### PostgreSQL behavior

Runs all suites against PostgreSQL 17. Database-specific behavior must not rely solely on SQLite.

### Frontend production build

Builds Vite assets using Node.js 22. It uses `npm ci` when `package-lock.json` exists. Until the lockfile is generated and committed on the development machine, CI emits a warning and uses `npm install`; this is transitional and not the desired stable state.

## Lockfile requirement

`composer.lock` and `package-lock.json` are required before a reproducible release. They cannot be fabricated safely in an offline packaging environment. Generate them on the development machine using the approved dependency versions, review the diffs, commit them, then switch every CI install to strict lockfile mode.

## Failure ownership

- documentation/alignment/source/CI verifier failure: foundation ownership;
- Pint/Larastan failure: file owner fixes the source, not the gate;
- SQLite-only failure: framework compatibility or test isolation;
- PostgreSQL-only failure: schema/query/database semantics;
- frontend build failure: asset/toolchain ownership;
- provider test requiring network access: invalid test design unless explicitly classified as an integration suite.

# Stage 11.7 Task Contract — Testing and CI Gates

## Goal

Make every documented quality boundary executable locally and in CI, with explicit separation between static quality, SQLite tests, PostgreSQL tests and frontend production builds.

## In scope

- register Unit, Architecture and Feature suites in PHPUnit;
- expose focused Composer test commands;
- verify CI configuration as source code;
- split GitHub Actions into independent quality, database and frontend jobs;
- run the supported PHP range against SQLite;
- run the full suite against PostgreSQL 17;
- preserve Pint, Larastan, documentation, alignment and source hygiene gates;
- document the temporary lockfile limitation without hiding it.

## Out of scope

- browser/E2E infrastructure;
- code coverage thresholds;
- mutation testing;
- live provider tests;
- deployment automation;
- generating dependency lockfiles without resolving dependencies on the development machine.

## Acceptance criteria

- `tests/Architecture` is registered in `phpunit.xml`;
- `composer test:unit`, `test:architecture`, `test:feature` and `test:all` exist;
- `composer quality:verify` validates CI configuration;
- CI has independent `quality`, `tests-sqlite`, `tests-postgres` and `frontend-build` jobs;
- SQLite tests run on PHP 8.3 and 8.4;
- PostgreSQL tests run against PostgreSQL 17;
- frontend installation uses `npm ci` when a lockfile exists and clearly warns/falls back otherwise;
- no provider network call or new package is introduced;
- governing documentation is reconciled.

## Verification

```bash
composer quality:normalize
composer quality:verify
composer test:all
npm run build
composer verify
```

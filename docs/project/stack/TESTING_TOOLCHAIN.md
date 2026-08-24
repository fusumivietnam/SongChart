# Testing Toolchain

## Suites

- Unit: domain rules and isolated transformations.
- Architecture: source and authority guardrails.
- Feature: routes, database behavior, framework integrations and workflows.

## Commands

- `composer test:unit`
- `composer test:architecture`
- `composer test:feature`
- `composer test:postgres`
- `composer quality:verify`
- `composer canonical:verify`

Database-dependent Feature tests use `RefreshDatabase`. New infrastructure or package ownership rules require Architecture tests. PostgreSQL CI is mandatory for schema and import-related work.

## Stage 16.4.1 taxonomy enforcement

`tests/Unit` does not boot Laravel and must not call Laravel application helpers or facades. Framework/package/database/Redis/Gate behavior belongs in Feature/Integration tests. Architecture tests enforce dependency and governance boundaries.

Run `composer test-taxonomy:verify` before packaging.


## Canonical verification lane

`compose.verify.yml` is the reproducible closure lane. It mounts the source tree but isolates `vendor/`, `node_modules`, and Composer cache in Docker named volumes. `scripts/canonical-verify.sh` restores lockfile dependencies, runs PostgreSQL 18 verification, normalizes with the locked Pint binary, and executes `composer canonical:verify`.

Windows entry point:

```bat
verify-songchart.bat
```

This lane is a release/candidate authority, not a replacement for Laragon development.

# Testing Toolchain

## Suites

- Unit: domain rules and isolated transformations.
- Architecture: source and authority guardrails.
- Feature: routes, database behavior, framework integrations and workflows.

## Commands

During iteration prefer the repository focused lane:

```bash
./songchart dev test tests/Unit/...
./songchart dev test tests/Architecture/...
./songchart dev test tests/Feature/...
```

Composer-owned suites remain available inside the governed Docker runtime:

- `composer test:unit`
- `composer test:architecture`
- `composer test:feature`
- `composer test:postgres`
- `composer quality:verify`
- `composer canonical:verify`

Database-dependent Feature tests use the isolated PostgreSQL verification database. New infrastructure or package ownership rules require Architecture coverage. PostgreSQL CI is mandatory for schema and import-related work.

## Taxonomy enforcement

`tests/Unit` does not boot Laravel and must not call Laravel application helpers or facades. Framework/package/database/Redis/Gate behavior belongs in Feature/Integration tests. Architecture tests enforce dependency and governance boundaries.

Run the taxonomy verifier through the repository Docker runtime when affected.

## Canonical verification lane

`compose.verify.yml` is the reproducible closure lane. It mounts the source tree but isolates `vendor/`, `node_modules`, and Composer cache in Docker named volumes. `scripts/canonical-verify.sh` restores lockfile dependencies, validates the canonical PHP/runtime contract, normalizes with the locked Pint binary, and executes `composer canonical:verify` exactly once.

The supported host entrypoint is Linux/WSL2:

```bash
./songchart candidate
./songchart verify
```

GitHub Codespaces uses the same repository contract. Native Windows Batch/PowerShell and Laragon execution paths are retired.

# Stage 16.5.4 Validation Report — Docker-First Development Environment Consolidation

Status: implementation candidate v22; canonical target closure pending.

## Implemented

- Docker development promoted to primary runtime authority.
- Laragon downgraded to compatibility-only.
- unified `songchart` Windows/PowerShell CLI.
- isolated Docker routine stage closure through `songchart test`.
- canonical closure remains `songchart verify`.
- legacy Docker wrappers converted to shims.
- machine-readable Docker development authority and guards.
- existing dev DB name/volumes intentionally preserved.

## Not claimed in packaging environment

Docker Desktop runtime, locked vendor/node_modules, PostgreSQL/Pest execution, and canonical evidence are target-only and are not claimed here.

## Packaging-side evidence

Passed in the implementation packaging environment:

- Docker-first development contract verifier;
- existing Docker local/trusted HTTPS verifier;
- runtime contract verifier;
- repository contract compiler;
- regression ledger (27 guarded classes);
- candidate/authority dependency/impact/AI/documentation/repository/package/source/static governance gates;
- PHP syntax for new PHP verifier/Architecture test;
- JSON parsing for new/changed machine-readable authorities.

Not run/claimed here: target Docker Compose execution, locked Pint/PHPStan/Pest, PostgreSQL stage lane, frontend build, canonical evidence/provenance.


## v22.1 corrective — architecture test no longer owns contract schema versions

The first v22 canonical run passed 281/282 tests. The only failure was `RepositoryContractSafetyClosureTest`, which required every machine-readable repository contract to remain `schema_version = 1`.

`runtime-environments.json` is legitimately schema version 2 in Stage 16.5.4. Exact schema-version compatibility belongs to each contract/verifier, not to a generic Architecture assertion whose purpose is only to ensure contracts remain machine-readable.

v22.1 therefore requires each listed contract to decode as an array and expose an integer `schema_version >= 1`, while specialized runtime/package/model/installer verifiers continue to own their own semantics.

Regression `REG-028 architecture-contract-schema-version-ownership-drift` records this class.

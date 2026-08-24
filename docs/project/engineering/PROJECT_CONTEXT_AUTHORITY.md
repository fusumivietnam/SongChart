# Project Context Authority

## Purpose

SongChart exposes a machine-readable project context so automation and AI agents read repository facts before changing persistence, Docker, provider, migration, seeder, verification, or architecture surfaces.

The context is derived from existing authorities. It is not a second hand-maintained source of truth.

## Command

Human-readable context:

```powershell
.\songchart.bat context
```

Machine-readable live context:

```powershell
.\songchart.bat context --json
```

Refresh the committed source-only machine manifest after changing a context input:

```powershell
.\songchart.bat context --refresh-source
```

AI environments that cannot start Docker may read `docs/project/generated/project-context.json`; canonical verification rejects this generated manifest when its source hashes are stale.

Persist the latest runtime snapshot when useful for local diagnosis:

```powershell
.\songchart.bat context --write
```

The persisted runtime snapshot is diagnostic state under `storage/project-state/project-context.json`; repository authorities remain the source of truth.

## Inputs

The context builder derives facts from:

- `composer.json` and `composer.lock`;
- `docs/project/stack/runtime-environments.json`;
- `docs/project/domain/schema-ownership.json`;
- `database/migrations/*.php`;
- `database/seeders/*.php`;
- `compose.dev.yml` and `compose.verify.yml`;
- `scripts/songchart.ps1`;
- `candidate-verification.json` for informational current-candidate display only; volatile candidate evidence is intentionally excluded from the source fingerprint.

When PostgreSQL is reachable through the Docker application environment, the context additionally inspects the actual PostgreSQL schema and reports missing migration-owned tables or columns.

## Drift rule

A source/runtime disagreement is a drift defect. Feature work must not guess around it. Correct the authority, code, migration, runtime schema, or command surface that is stale, then regenerate/re-read context.

The first version intentionally checks presence drift rather than attempting to infer every SQL type transformation from Laravel migration source. Existing model/schema, package/schema, migration lifecycle, PostgreSQL-major, and database-authority verifiers remain authoritative for their deeper contracts.

## AI rule

Before modifying architecture, persistence, Docker, providers, migrations, seeders, or verification infrastructure, read `songchart context --json`. Do not infer class names, schema ownership, Compose services, table presence, seeder FQCNs, or command availability when the generated context provides them. If source authorities and runtime disagree, report drift and fix it before feature work.

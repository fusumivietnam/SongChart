# Project Context Authority

Status: active derived-context authority.

## Purpose

SongChart exposes a machine-readable project context so developers and AI agents can inspect repository facts before modifying architecture, persistence, Docker, providers, migrations, seeders, or verification infrastructure.

Project context is derived from existing repository authorities. It is not an independent or manually maintained source of truth.

## Authority model

```text
repository authorities
        ↓
scripts/project-context.php
        ↓
docs/project/generated/project-context.json
        ↓
AI / automation / verification consumers
```

Generated context must never become a parallel authority. If generated context disagrees with its source authorities, it is stale and must be regenerated.

## Commands

Human-readable context:

```bash
./songchart context
```

Machine-readable context:

```bash
./songchart context --json
```

Persist a local diagnostic runtime snapshot:

```bash
./songchart context --write
```

Refresh committed source-derived context:

```bash
./songchart context --refresh-source
```

The normal governed refresh path during stage closure is:

```bash
./songchart candidate --prepare
```

AI environments that cannot inspect a live runtime may read `docs/project/generated/project-context.json`. Canonical verification rejects stale generated context.

## Registered source inputs

Project context derives stable repository facts from registered inputs including:

- `composer.json` and `composer.lock`;
- root `songchart` Linux CLI;
- `compose.dev.yml` and `compose.verify.yml`;
- `docs/project/stack/runtime-environments.json`;
- `docs/project/domain/schema-ownership.json`;
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`;
- `docs/project/engineering/ai-development-contract.json`;
- `docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md`;
- `docs/project/engineering/verification-command-surface.json`;
- `docs/project/engineering/verification-topology.json`;
- `docs/project/stack/release-pipeline-contract.json`;
- `database/migrations/*.php`;
- `database/seeders/*.php`.

Generation and verification must use the same registered source set. Retired platform adapters must not remain registered merely to preserve old hashes.

## Command surface

The active project CLI authority is the repository-root Linux entrypoint `./songchart`.

Supported development commands are derived from that CLI and currently include setup, ready, up, down, status, logs, shell, url, and test.

Project context is exposed through `./songchart context ...`; canonical verification is exposed through `./songchart verify`.

Native Windows Batch, PowerShell, Laragon, and ZIP handoff command surfaces are retired and are not project-context authorities.

## Runtime and database context

Runtime versions and environment ownership come from stack authorities rather than from manually duplicated prose.

Project context may project PHP/PostgreSQL authority, installed package versions, Compose services, migration-managed tables, schema ownership, seeders, and optional live PostgreSQL observations.

Runtime inspection is diagnostic evidence. It never silently rewrites committed repository authority.

## Source fingerprint

The committed generated context contains hashes for registered stable source inputs. Text inputs are normalized for line endings before hashing so equivalent LF/CRLF representations do not create false drift.

Verification must fail when a registered input changed, disappeared, was replaced without registration, or remains in generated context after retirement.

The remediation is regeneration, not suppression and not hand-editing generated JSON.

## Drift rules

### Source-to-generated drift

A registered source changed after `docs/project/generated/project-context.json` was generated.

Resolve with:

```bash
./songchart candidate --prepare
```

### Authority drift

Generation and verification disagree about source inputs, command surface, schema ownership, or another contract.

Correct the owning source and verifier together. Do not edit generated output to make the gate pass.

### Runtime drift

Committed authority and Docker/PostgreSQL runtime disagree.

Determine whether source, migration lifecycle, configuration, or runtime is stale and correct the owning layer.

## AI development rule

Before changing architecture, persistence, Docker, providers, migrations, seeders, or verification infrastructure, read:

```bash
./songchart context --json
```

Do not infer class names, seeder FQCNs, Compose services, schema ownership, expected migration tables, supported CLI commands, or runtime authority when project context already provides those facts.

If context reports drift, resolve it before making assumptions that depend on the disputed fact.

## Relationship to development state

Project context describes repository structure and derived technical facts. It does not own development progress.

Current stage, blockers, verification evidence, and next required action belong in `docs/project/DEVELOPMENT_STATE.md`.

Accepted chronology belongs in `docs/project/DEVELOPMENT_HISTORY.md`. Future direction belongs in `docs/project/docs/ROADMAP.md`.

`README.md` is a durable project introduction and must not become a project-context or development-state authority.

## Non-goals

Project context does not replace domain authorities, runtime authorities, schema ownership, migration lifecycle verification, lockfiles, candidate/canonical verification, or development-state ownership. It does not preserve retired platform command surfaces or automatically repair runtime drift.

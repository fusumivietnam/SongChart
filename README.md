# SongChartWeb

Current stage: **18.1 — Rich Entity & Multi-Provider Evidence Model**

Candidate delivery: **v1**

SongChartWeb is a Laravel 13 modular monolith for music discovery, canonical metadata, provider navigation, and editorial operations.

## Current development stage

- Product: `SongChart 0.1.0-dev`
- Stage: `18.1 — Rich Entity & Multi-Provider Evidence Model`
- Current-stage history: `docs/project/DEVELOPMENT_HISTORY.md`
- Domain authorities: `docs/project/domain/`
- Candidate closure: `composer stage:verify`
- Canonical closure: `composer canonical:verify`

Stage 18.1 extends provider normalization into a richer multi-source evidence envelope, adds provider-specific mapping and a read-only Admin import preview, and keeps all provider-derived data behind validation, identity and governed canonical-admission boundaries before any canonical mutation.

## Requirements

- PHP 8.5+
- Composer 2
- Node.js 22 or an approved LTS
- PostgreSQL 18.x (major 18 required for release verification)
- Linux/WSL2 source with Docker Engine + Compose v2 is the primary development runtime
- Laragon is compatibility-only

## Read before implementation

1. `PROJECT_AUTHORITY.md`
2. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
3. `AGENTS.md`
4. `docs/START_HERE.md`
3. `docs/DOCUMENTATION_GOVERNANCE.md`
4. `docs/DOCUMENTATION_INDEX.md`
5. `docs/project/docs/ENGINEERING_WORKFLOW.md`
6. the owning module authority and current task contract

Do not invent database fields, route semantics, provider states, or cross-entity mappings. Update the relevant executable contract first.

## Linux-first host workflow

Primary working tree:

```text
~/src/songchart
```

Primary host CLI:

```bash
./songchart dev setup
./songchart dev up
./songchart artisan admin:create
./songchart candidate
./songchart test
./songchart verify
```

Stable Compose identities are `songchart-dev` for persistent development data and
`songchart-verify` for isolated verification. Repository folder renames therefore
do not silently create a new development database identity.

## Local setup

For an existing development tree with reviewed lockfiles:

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan songchart:setup-local --demo
```

Create the first administrator interactively:

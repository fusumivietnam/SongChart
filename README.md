# SongChartWeb

SongChartWeb is an open music knowledge and discovery platform built around trustworthy canonical metadata, multi-provider evidence, relationships, provenance, and destinations for listening or watching music.

Rather than treating one external provider as the source of truth, SongChart builds a provider-neutral canonical catalog and keeps imported provider data behind normalization, validation, identity resolution, provenance, and governed admission boundaries.

## What SongChart does

SongChart is designed around several connected capabilities:

- canonical artists, groups, recordings, releases, works, and relationships;
- evidence collected from multiple music-data providers;
- deterministic provider normalization and identity resolution;
- provenance-aware metadata and operational review;
- search and discovery over canonical entities;
- media and provider destinations for listening and watching;
- editorial and operational administration;
- provider ingestion, preview, import, quarantine, and health workflows;
- explicit authorization and privileged-operation auditing.

## Architecture

SongChart is a Laravel 13 modular monolith.

Core architectural rules include:

- canonical entities remain provider-neutral;
- provider identifiers never become canonical primary identity;
- external data enters through provider contracts and adapters;
- imported evidence is normalized and validated before canonical admission;
- controllers remain transport adapters;
- read composition belongs in application queries/read models;
- mutations belong in explicit application or domain services;
- Laravel Gates own authorization;
- PostgreSQL is the release-authoritative database;
- historical migrations are immutable after sealing.

## Technology

The repository runtime is Docker-first:

- PHP 8.5
- Laravel 13
- PostgreSQL 18
- Redis
- Node 24
- Blade / Livewire / Alpine
- Vite
- Pest
- PHPStan / Larastan
- Laravel Pint

Development uses Linux/WSL2 or GitHub Codespaces with Docker Engine and Compose v2.

Native Windows Batch/PowerShell and Laragon workflows are retired.

## Getting started

Clone the repository and use the repository CLI:

```bash
./songchart dev setup
./songchart dev ready
./songchart dev status
```

In GitHub Codespaces:

```bash
./songchart dev url
```

Run application commands through the same environment:

```bash
./songchart artisan migrate
./songchart composer install
./songchart npm run build
```

## Testing and verification

Run focused tests while developing:

```bash
./songchart dev test tests/Unit/...
./songchart dev test tests/Architecture/...
./songchart dev test tests/Feature/...
```

Inspect repository context:

```bash
./songchart context --json
```

Candidate and canonical verification:

```bash
./songchart candidate
./songchart verify
```

Generated repository authority is refreshed only when necessary:

```bash
./songchart candidate --prepare
```

## Repository authorities

Start with:

- `PROJECT_AUTHORITY.md` — repository engineering authority;
- `docs/START_HERE.md` — documentation routing;
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md` — AI/development workflow;
- `docs/project/domain/` — domain and persistence contracts;
- `docs/project/stack/` — runtime and stack contracts;
- `docs/project/security/` — authorization/security contracts;
- `docs/project/engineering/` — verification and engineering contracts.

For the current development checkpoint, see `docs/project/DEVELOPMENT_STATE.md`.

Accepted history is recorded in `docs/project/DEVELOPMENT_HISTORY.md`.

Future product direction lives in `docs/project/docs/ROADMAP.md`.

## Development philosophy

SongChart favors explicit authority over duplicated conventions:

- Laravel/native capabilities before custom infrastructure;
- machine-readable contracts before copied prose rules;
- Git as the development source of truth;
- focused tests during iteration;
- exact-tree candidate and canonical verification before merge;
- no weakening PHPStan, Pest, PostgreSQL, CI, or architecture gates to make a change pass.

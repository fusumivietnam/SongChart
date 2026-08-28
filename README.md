# SongChartWeb

SongChartWeb is an open music knowledge and discovery platform built around trustworthy canonical metadata, multi-provider evidence, relationships, provenance, and destinations for listening or watching music.

Rather than treating one external provider as the source of truth, SongChart builds a provider-neutral canonical catalog and keeps imported provider data behind normalization, validation, identity resolution, provenance, and governed admission boundaries.

## First 10 minutes for developers

SongChart is a Laravel 13 modular monolith with a Docker-first development workflow. If you are new to the repository, do this first:

```bash
./songchart dev setup
./songchart dev ready
./songchart ai doctor
./songchart context --json
```

In GitHub Codespaces:

```bash
./songchart dev url
```

Then read, in this order:

1. `PROJECT_AUTHORITY.md` — durable engineering authority;
2. `docs/project/DEVELOPMENT_STATE.md` — what is done, active now, next, blocked, and the next required action;
3. the active stage task contract under `docs/foundation/`;
4. `docs/START_HERE.md` / `docs/DOCUMENTATION_INDEX.md` to locate the owning domain or engineering authority;
5. `docs/project/engineering/ENGINEERING_GRAPH.md` when you need to understand how authorities, impact, verification, regressions and delivery connect.

Do not start by reading historical stage documents. They are evidence, not current navigation authority.

## Source map — where should code go?

Use the existing owner before creating a new abstraction.

```text
HTTP / CLI / JOB
      |
      v
APPLICATION USE CASE
      |
      +--> query/read model ------> MODEL / POSTGRES
      |
      `--> action/write service --> DOMAIN / MODEL / EXTERNAL ADAPTER

DOMAIN
  business semantics and invariants

APPLICATION
  use-case orchestration, queries/read models, commands/actions

HTTP
  transport only: request, validation/authorization handoff, response

MODELS
  persistence representation; not a shortcut around application boundaries

SUPPORT
  adapters, infrastructure and cross-cutting implementations already owned there

SERVICES
  keep only when a real service/integration responsibility exists; do not use as a generic dumping ground
```

Current major product areas:

- `app/Domain/Catalog` — canonical music semantics;
- `app/Domain/Providers` + `app/Contracts/Providers` + `app/Support/Providers` — provider ingestion/runtime integration;
- `app/Application/Admin`, `app/Support/Admin`, `app/Http/Controllers/Admin` — governed administrator use cases and presentation;
- `app/Application/Discovery` / `app/Domain/Discovery` — search/discovery behavior;
- `app/Domain/Extensions` / `app/Actions/Admin/Extensions` — extension lifecycle;
- `app/Support/Auth` and Laravel Gates — authorization;
- `app/Support/Engineering` + `docs/project/engineering/` — repository/verification tooling, not product logic.

Before adding a new `Service`, `Support` class, verifier, contract, or command, confirm that no existing owner can express the invariant.

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

Core architectural rules:

- canonical entities remain provider-neutral;
- provider identifiers never become canonical primary identity;
- external data enters through provider contracts and adapters;
- imported evidence is normalized and validated before canonical admission;
- controllers remain transport adapters;
- read composition belongs in application queries/read models;
- mutations belong in explicit application actions/write services;
- Laravel Gates own authorization;
- PostgreSQL is the release-authoritative database;
- historical migrations are immutable after sealing;
- generated repository authority is derived from source and must be regenerated, not hand-edited;
- AI/developer learning is navigation/debug evidence only; durable rules belong to existing authorities and permanent guards.

Provider/canonical flow:

```text
OFFICIAL PROVIDER API
        |
        v
ADAPTER / RATE / CREDENTIAL BOUNDARY
        |
        v
NORMALIZED PROVIDER EVIDENCE
        |
        v
IMPORT / QUARANTINE / IDENTITY RESOLUTION
        |
        v
CANONICAL ADMISSION
        |
        v
PROVIDER-NEUTRAL CATALOG
```

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

Development uses Linux/WSL2 or GitHub Codespaces with Docker Engine and Compose v2. Native Windows Batch/PowerShell and Laragon workflows are retired.

## Everyday development workflow

Before a coherent change:

```bash
./songchart ai doctor
./songchart context --json
./songchart impact <planned-path> [planned-path...]
```

Implement the smallest coherent source + authority + regression slice. Then:

```bash
./songchart impact --diff
./songchart reconcile   # only when registered generated inputs changed
./songchart audit       # collect-all diagnostic feedback
```

Run focused tests while iterating:

```bash
./songchart dev test tests/Unit/...
./songchart dev test tests/Architecture/...
./songchart dev test tests/Feature/...
./songchart composer exec pint -- --test
./songchart composer exec phpstan analyse
```

Canonical full verification remains available through the repository entrypoint:

```bash
./songchart verify
```

Close only after the stage is actually ready:

```bash
./songchart candidate
./songchart close

git rev-parse HEAD
git status --short
```

Any tracked change after canonical PASS invalidates the previous closure evidence. Push and merge the exact closed HEAD only.

## Current work and collaboration

The single operational checkpoint is:

- `docs/project/DEVELOPMENT_STATE.md` — done / now / next / blockers / latest evidence / next action.

When another developer or AI takes over:

1. sync the current stage branch;
2. read `DEVELOPMENT_STATE.md`;
3. run `./songchart ai doctor`;
4. inspect `./songchart impact --diff` before assuming scope;
5. avoid editing the same source surface as another active writer until handoff by exact commit SHA.

AI-specific development guidance lives in `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md` and `.agents/skills/`. AI learning exists only to improve repository navigation and debugging; it must not create parallel product/runtime rules.

## Repository authorities

Use `docs/START_HERE.md` and `docs/DOCUMENTATION_INDEX.md` instead of browsing documentation chronologically.

Key owners:

- `PROJECT_AUTHORITY.md` — repository engineering authority;
- `docs/project/domain/` — domain, schema, use-case and application-data boundaries;
- `docs/project/stack/` — runtime, package, database and migration contracts;
- `docs/project/security/` — authorization/security contracts;
- `docs/project/engineering/` — verification, impact, AI/development and delivery contracts;
- `docs/providers/` — provider behavior/compliance;
- `docs/ui/` — UI contracts;
- `docs/operations/` — operator/runtime procedures.

Accepted history is in `docs/project/DEVELOPMENT_HISTORY.md`. Future product direction is in `docs/project/docs/ROADMAP.md`.

## FAQ for developers

### I want to add or change a feature. Where do I start?

Start from the use case, then find its existing Domain/Application owner and run `./songchart impact` on the planned paths. Do not start by creating a controller, service, migration, or verifier.

### Where should database reads go?

Use an existing registered application query/read-model surface. Controllers must not open direct query/persistence boundaries.

### Where should writes go?

Use an explicit application action/command or an already-owned domain/support write service. Preserve transactions, authorization and audit invariants at the existing owner.

### Can I add a new `Service` or put logic under `Support`?

Only when the responsibility is genuinely not owned by an existing Domain/Application/adapter surface. `Support` and `Services` are not generic fallback folders.

### How do I change provider behavior?

Read the provider authority and the official provider/API documentation first. Trace adapter → rate/credential policy → normalized evidence → import/identity/admission. Do not let provider IDs or payload shape leak into canonical identity.

### Do I need a new verifier for every rule?

No. Prefer extending the existing semantic authority and its existing durable guard. Add a verifier only when the invariant cannot be reliably enforced by the current owner/test surface.

### What should I do when a gate fails?

Preserve the exact failure, identify its owning authority, classify whether source/consumer/generated/runtime state is wrong, fix the owner, add focused regression when reusable, then rerun `./songchart impact --diff`. Do not patch assertions until they turn green without understanding ownership.

### What does AI "learn" in this repository?

Only development navigation/debug patterns backed by evidence. A durable rule is valid only after it is promoted into the real repository authority and protected by a permanent guard. AI learning never overrides source, domain contracts, framework/provider official documentation or runtime authority.

### What can be safely deleted?

Do not delete a file merely because it looks historical or redundant. First prove it has no active authority, runtime, build, documentation-navigation or verification consumer. Historical `STAGE_*` evidence may be archived/retired from active navigation, but active machine contracts, generated authorities and compatibility files must follow their owning verifier/consumer graph.

### Why are there many contracts and verification files?

They encode failure classes that previously escaped to late verification. New work should reduce duplication: one invariant → one semantic owner → the minimum durable guards needed. Do not create parallel standards.

## Development philosophy

SongChart favors explicit ownership over duplicated conventions:

- official/native capabilities before custom infrastructure;
- one semantic owner per invariant;
- graph/impact navigation instead of repository-wide guessing;
- machine-readable contracts for machine-critical state;
- Git as the development source of truth;
- focused tests during iteration;
- exact-tree candidate and canonical verification before merge;
- remove obsolete aliases, duplicated prose and dead compatibility surfaces once their consumers are proven retired;
- no weakening PHPStan, Pest, PostgreSQL, CI, authorization, audit or architecture gates to make a change pass.

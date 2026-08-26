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

```powershell
php artisan admin:create
```

For an existing local administrator, use the idempotent command instead:

```powershell
php artisan admin:ensure-local your-email@example.com
```

Local development defaults `SONGCHART_ADMIN_2FA_MODE=disabled`; staging/production still require confirmed 2FA. No administrator password is stored in the repository or created by the default seeder.

Useful local URLs:

```text
http://songchart.test/
http://songchart.test/login
http://songchart.test/account/security
http://songchart.test/admin
http://songchart.test/development/status
```

## Contract authorities

- `docs/project/domain/domain-contracts.json` — canonical catalog entity/field/identifier authority.
- `docs/project/domain/operational-contracts.json` — provider, ingestion, identity, provenance, and account read-surface authority.
- `docs/project/domain/use-case-contracts.json` — route, actor, middleware, implementation, and exact read/write surface authority.
- `docs/project/domain/schema-ownership.json` — one-owner classification for every migration-managed application/framework table.

Run:

```bash
composer domain-contracts:verify
composer operational-contracts:verify
composer schema-ownership:verify
composer runtime-authority:verify
composer use-case-contracts:verify
composer type-guardrails:verify
composer impact-map:verify
```

## Development and release commands

```bash
php scripts/resolve-repository-impact.php <changed-paths>
composer quality:normalize
composer quality:verify
composer stage:verify
composer canonical:verify
composer release:package
```

Use focused impact-driven gates while iterating. `composer stage:verify` owns candidate closure; Docker owns `composer canonical:verify`; packaging is allowed only after canonical/provenance PASS. Removed legacy aliases must not be reintroduced.

Verified release source is packaged only after canonical/provenance PASS with `composer release:package`.

## Canonical verification environment

Docker Desktop + WSL2 is the supported primary local development runtime. Release verification uses the isolated Docker verification profile and named volumes for Composer `vendor/`, `node_modules`, and Composer cache. Laragon is compatibility-only.

On Windows with Docker Desktop / WSL2:

```bat
verify-songchart.bat
```

Equivalent PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/verify-canonical.ps1
```

The canonical verification environment runs PHP 8.5, Node 22, PostgreSQL 18 and Redis, installs dependencies from lockfiles, normalizes with the locked Pint version, and then runs `composer canonical:verify` exactly once.

## Docker local development

After the canonical verification lane is working, Docker Desktop + WSL2 can also host an isolated HTTPS development site without replacing Laragon.

First-time setup (run from an Administrator PowerShell because the hosts file may need updating):

```bat
docker-dev-setup.bat
```

Then use:

```bat
docker-dev-up.bat
docker-dev-down.bat
```

Default endpoint:

```text
https://docker.songchart.test:8443
```

The profile uses PostgreSQL 18.4, Redis, Caddy 2.11.3, a Docker-only dependency volume, and a trusted mkcert certificate. Laragon can continue serving `https://songchart.test` on port 443 at the same time.

## Privileged operations and audit

Stage 16.5 introduces an explicit business audit trail for privileged administration. `spatie/laravel-activitylog` is used as infrastructure, but SongChart deliberately does not attach broad automatic model logging traits.

Audited surfaces include provider state/recovery operations, identity-conflict decisions, extension lifecycle operations, and privileged user role/activation commands.

Operations administrators can inspect the audit stream at:

```text
/admin/audit
```

Privileged user changes must use audited commands instead of Tinker:

```bash
php artisan admin:user:set-role user@example.com editor --actor=admin@example.com --reason="Approved editorial responsibility change."
php artisan admin:user:set-active user@example.com inactive --actor=operator@example.com --reason="Account suspended after access review."
```

## Repository contract and release safety

Stage 16.5.1 converts recurring failures into permanent machine gates. It verifies historical regression guards, package/model schema contracts, Feature-test isolation, runtime profiles, lockfile authority and release orchestration before PostgreSQL/runtime closure.

Primary static entry point:

```bash
composer repository-contracts:verify
```

Release closure additionally requires both dependency lockfiles and the real PostgreSQL migration contract.

## Documentation and history

- Current documentation ownership: `docs/DOCUMENTATION_INDEX.md`
- Delivered Stage 16 chronology: `docs/project/DEVELOPMENT_HISTORY.md`
- Future roadmap only: `docs/project/docs/ROADMAP.md`
- Historical Stage 11/12 delivery records remain frozen in their original manifests.

Correction note: verifier TestCase annotation detection now accepts fully-qualified and imported forms and reports the source SHA-256 on failure.

## Executable repository authority

Stage 16.5.2 resolves repository authorities through `RepositoryContractResolver` instead of allowing each verifier/test to maintain its own copy. Use `composer repository-compiler:verify` for closure and `php artisan songchart:doctor --contract=<name>` to inspect an authority and consumers.

## Stage delivery / fast upgrade

Every implementation stage is delivered in two synchronized artifacts:

- `songchart-<stage>-laragon-ready.zip` — full recovery/bootstrap source;
- `songchart-<from>-to-<stage>-changeset.zip` — preferred fast upgrade from the declared previous accepted baseline.

Keep the target project's `.env`. Normal upgrades should apply the changeset rather than delete/re-extract the full project. Changesets must not ship secrets or dependency/runtime state and must use Docker-first `.bat` verification. Mandatory rules: `docs/project/engineering/DELIVERY_WORKFLOW.md`.

## Verification workflow and AI protocol

Stage 16.5.3 removes nested duplicate verification. The executable topology is `docs/project/engineering/verification-topology.json`; AI workflow authority is `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`.

Model-specific AI files are bootstrap pointers only. Do not add stage-specific workflow copies to AGENTS/CLAUDE/GEMINI.

## Docker-first primary workflow

> **Execution authority:** On Windows, use `stage-verify.bat` (or `songchart.bat test`) as the iterative candidate gate. For final closure, run `verify-songchart.bat` (or `songchart.bat verify`) **by itself**; canonical verification already executes `@stage:verify` internally. Running both back-to-back is optional diagnostics, not the required workflow. Do not use host `composer stage:verify` as the normal verification path; that command is executed inside the Docker verification container.

The supported primary Windows workflow uses Docker Desktop/WSL2; host PHP, Composer, Node, PostgreSQL and Redis are not required.

```bat
songchart dev setup
songchart dev up
songchart dev status
songchart artisan migrate
songchart composer install
songchart npm run build
songchart test
songchart verify
```

`compose.dev.yml` owns persistent development services/data. `compose.verify.yml` owns an isolated test/canonical database. Existing `docker-dev-*.bat` and `verify-songchart.bat` commands are retained as compatibility shims. Laragon remains available only for compatibility and must not be used for release claims.

## Verification command surface reduction

Stage 16.5.5 removes legacy Composer aliases and historical release orchestration from active tooling. Use only:

```text
songchart test
composer stage:verify
songchart verify
composer canonical:verify
composer release:package
```

Machine authority: `docs/project/engineering/verification-command-surface.json`.

## Migration lifecycle and upgrade safety

Stage 16.5.6 freezes historical migrations through `docs/project/stack/migration-lifecycle-contract.json`. Existing schema repairs are forward-only.

Active checks:

```text
composer migration-lifecycle:verify
composer migration-upgrade:verify   # isolated PostgreSQL verification environment
songchart verify
```

Fresh installation remains covered by the normal PostgreSQL test lane. Canonical closure additionally reconstructs a supported previous-schema fixture and proves forward migration to the current schema.

## Verification consumer ownership

Stage 16.5.7 routes every active verifier and Architecture test through:

```text
docs/project/engineering/verification-consumer-graph.json
```

The repository compiler requires every verification consumer to match exactly one semantic ownership rule. New unowned or overlapping verification consumers fail before the stage PostgreSQL/Pest lane.

Before changing an authority or verification consumer:

```bash
php scripts/resolve-repository-impact.php <changed paths>
composer repository-compiler:verify
```

The impact output includes semantic verification ownership for changed verifier/Architecture paths.

## Authorization model

SongChart uses Laravel Gates over a static machine-owned role/capability matrix:

```text
docs/project/security/authorization-contract.json
        ↓
App\Support\Auth\AuthorizationMatrix
        ↓
App\Enums\Capability
        ↓
Laravel Gate
```

`UserRole` no longer defines `can*` methods and `User` no longer repeats capability logic. Privileged mutations authorize through Gates and then record explicit privileged audit events.

## Application data boundary

SongChart separates transport, read composition and mutation without imposing full CQRS:

```text
HTTP / Console / Jobs
        │
        ▼
Application
  Queries / Read Models  ── READ
  Actions / Commands     ── WRITE
        │
        ▼
Domain / Infrastructure
Eloquent · Query Builder · Redis · Provider adapters
```

Controllers do not compose persistence queries or mutate storage directly. Read models may use Eloquent/Query Builder for efficient projections. Writes keep transaction/locking ownership in explicit application/domain services.

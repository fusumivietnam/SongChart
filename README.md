# SongChartWeb

Current stage: **18.1 — Rich Entity & Multi-Provider Evidence Model**

Candidate delivery: **v1**

SongChartWeb is a Laravel 13 modular monolith for music discovery, canonical metadata, provider navigation, and editorial operations.

## Current development stage

- Product: `SongChart 0.1.0-dev`
- Stage: `18.1 — Rich Entity & Multi-Provider Evidence Model`
- Current checkpoint: `docs/project/DEVELOPMENT_STATE.md`
- Delivered chronology: `docs/project/DEVELOPMENT_HISTORY.md`
- Future roadmap: `docs/project/docs/ROADMAP.md`
- Candidate closure: `./songchart candidate`
- Canonical closure: `./songchart verify`

Stage 18.1 extends provider normalization into a richer multi-source evidence envelope, adds provider-specific mapping and a read-only Admin import preview, and keeps provider-derived data behind validation, identity and governed canonical-admission boundaries before canonical mutation.

## Runtime requirements

The supported development and verification workflow is Linux/WSL2 + Docker + Git.

- Docker Engine with Compose v2
- Git
- PHP 8.5 inside the repository image
- Composer 2 inside the repository image
- Node 24 inside the repository image
- PostgreSQL 18 for release-authoritative persistence
- Redis for queue/runtime infrastructure

GitHub Codespaces is the preferred remote development adapter. Native Windows Batch/PowerShell and Laragon execution paths are retired; Windows development uses WSL2 and the same Linux `./songchart` entrypoint.

## Read before implementation

1. `PROJECT_AUTHORITY.md`
2. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
3. `docs/project/DEVELOPMENT_STATE.md`
4. the current stage task contract
5. the owning domain/module authority
6. `docs/project/engineering/DELIVERY_WORKFLOW.md` before handoff/closure

Model-specific `AGENTS.md`, `CLAUDE.md`, and `GEMINI.md` are thin bootstrap pointers only.

## Development workflow

GitHub is the source of truth. Do not copy project ZIPs between devices or use cross-device stash as a handoff mechanism.

```bash
./songchart ai status
./songchart ai doctor
./songchart dev setup
./songchart dev ready
./songchart dev up
./songchart dev status
./songchart dev test tests/Feature/...
./songchart artisan migrate
./songchart composer install
./songchart npm run build
```

Stable Compose identities:

- `songchart-dev` — persistent development data/runtime
- `songchart-verify` — isolated verification/canonical runtime

Local Docker endpoint:

```text
https://docker.songchart.test:8443
```

In GitHub Codespaces, the app is exposed through the private-by-default forwarded port 8000 URL reported by:

```bash
./songchart dev url
```

## Project context and impact

Before architecture, persistence, Docker, provider, migration, seeder, or verification-infrastructure changes:

```bash
./songchart context --json
```

Resolve planned source impact with:

```bash
php scripts/resolve-repository-impact.php <changed-paths>
```

If a registered project-context input changes, refresh generated source authority through the governed candidate preparation flow rather than maintaining a parallel truth source.

## Verification

During iteration, prefer focused tests:

```bash
./songchart dev test tests/Unit/...
./songchart dev test tests/Architecture/...
./songchart dev test tests/Feature/...
```

Candidate closure on the exact committed tree:

```bash
./songchart candidate --prepare   # only when generated authority needs refresh
./songchart candidate
```

Canonical closure only after candidate PASS:

```bash
./songchart verify
```

Release/deployment packaging is allowed only after canonical/provenance PASS:

```bash
composer release:package
```

`composer release:package` is not a development handoff mechanism.

## Contract authorities

Key machine-readable authorities include:

- `docs/project/domain/domain-contracts.json` — canonical catalog entity/field/identifier authority
- `docs/project/domain/operational-contracts.json` — provider, ingestion, identity, provenance, and account read-surface authority
- `docs/project/domain/use-case-contracts.json` — route, actor, middleware, implementation, and read/write surface authority
- `docs/project/domain/schema-ownership.json` — migration-managed table ownership
- `docs/project/domain/application-data-boundary.json` — transport/read/write persistence boundaries
- `docs/project/security/authorization-contract.json` — role/capability and Laravel Gate authority
- `docs/project/engineering/verification-topology.json` — verification lifecycle topology
- `docs/project/engineering/verification-command-surface.json` — supported command surface
- `docs/project/engineering/verification-consumer-graph.json` — verifier/Architecture consumer ownership
- `docs/project/stack/runtime-environments.json` — runtime profiles
- `docs/project/stack/docker-development-contract.json` — development/verification environment contract
- `docs/project/stack/migration-lifecycle-contract.json` — frozen migration and upgrade authority

## Architecture principles

- Laravel/native first; do not build parallel infrastructure when Laravel or an approved package owns the capability.
- PostgreSQL 18 is the release-authoritative database.
- Canonical models remain provider-neutral; provider IDs do not become canonical primary identity.
- Controllers are transport adapters; read composition belongs in registered read models/queries, and writes belong in explicit application/domain mutation surfaces.
- Laravel Gates own authorization over the machine-defined capability matrix.
- Privileged/business audit is explicit at use-case boundaries; secrets and raw sensitive provider payloads are never logged.
- Historical migrations are immutable after sealing; schema corrections are forward migrations.
- Do not weaken PHPStan/Larastan, Pint, Pest, PostgreSQL, CI, or candidate/canonical gates to make a stage pass.

## Privileged operations

Privileged user changes use audited application commands rather than Tinker:

```bash
./songchart artisan admin:user:set-role user@example.com editor --actor=admin@example.com --reason="Approved editorial responsibility change."
./songchart artisan admin:user:set-active user@example.com inactive --actor=operator@example.com --reason="Account suspended after access review."
```

Operations administrators can inspect the audit stream at `/admin/audit`.

## Documentation

- Entry authority: `PROJECT_AUTHORITY.md`
- Documentation index: `docs/DOCUMENTATION_INDEX.md`
- Engineering workflow: `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- Delivery/handoff: `docs/project/engineering/DELIVERY_WORKFLOW.md`
- Operational checkpoint: `docs/project/DEVELOPMENT_STATE.md`
- History: `docs/project/DEVELOPMENT_HISTORY.md`
- Roadmap: `docs/project/docs/ROADMAP.md`

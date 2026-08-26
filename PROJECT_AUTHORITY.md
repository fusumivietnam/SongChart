# SongChartWeb Project Authority

Status: mandatory engineering entry point. Machine-readable authorities referenced here take precedence over prose summaries.

## Runtime baseline

- PHP: `^8.5`
- Laravel: `^13.0`
- Database: PostgreSQL 18.x is the only release-authoritative database; release gates require major version 18 exactly.
- Queue: Laravel Redis queue is the runtime baseline.
- Primary development: Linux/WSL2 source with Docker Engine + Compose v2; GitHub Codespaces is the preferred remote adapter.
- Native Windows Batch/PowerShell and Laragon development runtimes are retired. Windows development, when needed, uses WSL2 and the same Linux `./songchart` entrypoint.
- Horizon: Linux deployment profile only.
- Pulse: first-party operational observability.
- Node: 24 / repository lockfile authority.

## Read order before implementation

1. `PROJECT_AUTHORITY.md`
2. `docs/START_HERE.md`
3. owning domain/module authority
4. current task contract
5. `docs/project/stack/package-registry.json`
6. `docs/project/stack/impact-test-map.json`

## Non-negotiable engineering rules

- Laravel/native first. Do not create parallel infrastructure when Laravel or an approved package owns the capability.
- Do not weaken PHPStan/Larastan, Pint, Pest, PostgreSQL or CI gates to make a stage pass.
- New stage code may not add PHPStan baseline entries.
- `tests/Unit` must remain framework-container independent: no `app()`, `config()`, `base_path()`, DB, Cache, Http, Storage or Artisan helpers.
- Framework/package integration belongs in Feature/Integration tests.
- Vendor-derived files committed to the repository must conform to SongChart static-analysis rules. Adapt fail-closed; do not suppress.
- PostgreSQL is the release authority. SQLite is compatibility-only where explicitly documented.
- Public Discovery requests read projections; they do not execute Discovery rules.
- Canonical models remain provider-neutral. Provider IDs do not become canonical primary identity.
- Provider compliance, normalization, identity resolution and Discovery semantics remain SongChart-owned domain capabilities.

## Package admission

A PHP package may be added only when:
1. official/version requirements are verified;
2. capability ownership is explicit;
3. OS/PHP/extensions/database compatibility is recorded;
4. published config/migrations are inspected;
5. SongChart static-analysis/test adaptation is defined;
6. exit/removal strategy is recorded;
7. `package-registry.json` and prose registry are updated;
8. package-admission verifier passes.

Run:

```bash
composer package-governance:verify
```

## Test taxonomy

### Unit

`tests/Unit` is pure isolated code. Laravel application/container and framework services are not available.

### Feature / Integration

Use for routes, Gates, service providers, package integration, database, Redis, queues and framework services.

### Architecture

Use for source boundaries, capability ownership, forbidden dependencies and governance rules.

### PostgreSQL

Schema, migrations, persistence and release-authoritative database behavior must pass the PostgreSQL lane.

## Delivery and handoff authority

`docs/project/engineering/DELIVERY_WORKFLOW.md` is mandatory for every implementation stage and corrective candidate.

- GitHub is the only development source of truth and handoff mechanism.
- A stage uses its stage branch; logical slices are committed and pushed rather than copied between machines.
- Device/AI handoff uses Git state plus `./songchart ai status` / `./songchart ai doctor` evidence.
- Do not publish or consume source ZIPs, incremental ZIPs, Laragon-ready ZIPs, patch installers, or cross-device stashes as normal development handoff.
- Do not advance implementation against an assumed target tree. The next action starts from the exact committed branch tree.
- `composer release:package` remains post-canonical release/deployment packaging only; it is not a source synchronization mechanism.

## Verification workflow

Verification topology authority: `docs/project/engineering/verification-topology.json`.

During implementation, use impact-driven focused gates. Do not reflexively run the full PostgreSQL/build closure after every small edit.

Candidate-stage closure has one owner:

```bash
composer stage:verify
```

It runs repository-wide quality/static closure, the authoritative PostgreSQL suite, and the frontend production build exactly once.

Canonical closure has one owner inside `compose.verify.yml`:

```bash
composer canonical:verify
```

The canonical shell installs locked Composer/npm dependencies, normalizes with the locked Pint version, then calls that command exactly once. `canonical:verify` invokes `stage:verify` once and owns remaining runtime/package/migration/evidence gates.

A stage is not closure-ready until canonical evidence is recorded and exact-tree provenance remains valid. Release packaging occurs only after canonical PASS:

```bash
composer release:package
```

## Verification command surface

Active workflow entrypoints are intentionally small:

```text
Development:       ./songchart dev ...
Focused Docker:    ./songchart dev test <path> / ./songchart test
Candidate closure: ./songchart candidate / composer stage:verify
Canonical closure: ./songchart verify / composer canonical:verify
Packaging:         composer release:package
```

Removed compatibility aliases such as `verify`, `release:verify`, `test:all`, `test:postgres-clean`, `delivery:verify`, and `release-contract:verify` must not be reintroduced. The machine authority is `docs/project/engineering/verification-command-surface.json`.

## Canonical verification environment

Linux/WSL2 Docker is the supported local verification host; GitHub Codespaces is a remote adapter over the same repository contract. Closure candidates must be verified in `compose.verify.yml` before packaging.

The canonical environment owns:
- PHP 8.5
- Composer 2 and lockfile-backed `vendor/`
- Node 24 and lockfile-backed `node_modules`
- PostgreSQL major 18
- Redis
- repository-locked Pint/Larastan/Pest execution

`vendor/` and `node_modules` use Docker named volumes. The source tree is bind-mounted so locked normalization and generated authority apply to the exact Git tree being verified.

Run:

```bash
./songchart candidate
./songchart verify
```

A candidate may be marked `closure_ready=true` only after the canonical environment completes `composer canonical:verify` and records evidence in `candidate-verification.json`.

## Docker-first development

`compose.dev.yml` is the primary local web-development profile. `compose.verify.yml` remains the isolated canonical/test profile. `compose.codespaces.yml` adapts the same development contract to GitHub Codespaces.

Primary commands:

```bash
./songchart dev setup
./songchart dev ready
./songchart dev up
./songchart dev test tests/Feature/...
./songchart candidate
./songchart verify
```

Local Docker endpoints are intentionally port-safe:

```text
https://docker.songchart.test:8443
http://docker.songchart.test:8080
```

Codespaces uses private-by-default GitHub forwarded port 8000 and does not require local Caddy/TLS. Native Windows wrappers and Laragon host ownership are not supported execution paths.

Trusted proxy handling is permitted only when `APP_ENV=local` and `SONGCHART_TRUST_DOCKER_PROXY=true`.

## Migration lifecycle authority

Historical migrations listed in `docs/project/stack/migration-lifecycle-contract.json` are frozen from a baseline sealed once on the exact Docker canonical target after locked Pint normalization. Packaging-side artifacts must never invent or overwrite that baseline. After sealing, immutability is enforced with semantic PHP-token fingerprints. Once a migration may have been applied to a persistent database, later schema repairs must be implemented as new forward migrations.

Required invariants:

- `migrate:fresh` is necessary but not sufficient for release safety;
- canonical verification must also prove a supported previous-schema → current PostgreSQL upgrade;
- package-managed schema adaptations must record both historical ownership and forward corrections;
- guarded forward repairs use explicit table/column existence checks when repairing known drift;
- never make an existing deployment depend on edits to an already-recorded migration.

## Application data boundary authority

`docs/project/domain/application-data-boundary.json` owns read/write persistence boundaries.

- Controllers are transport adapters: no direct Query Builder/Eloquent query composition and no persistence mutation.
- Application Query/Read Model surfaces own read composition and may use optimized Eloquent/Query Builder.
- Registered read models are read-only.
- Application Actions/Commands and explicit write services own mutation, transaction and locking behavior.
- Simple reads must not be hidden behind ceremonial repository abstractions.
- External provider access remains behind provider contracts/adapters.
- `docs/project/performance/query-budget-contract.json` owns request/read-model query-budget registration. Hard query limits require representative PostgreSQL evidence.

## Authorization authority

`docs/project/security/authorization-contract.json` is the machine authority for SongChart role→capability semantics. Laravel Gates are the runtime authorization mechanism.

- Gate names come from `App\Enums\Capability`.
- `App\Support\Auth\AuthorizationMatrix` loads and validates the static matrix.
- inactive users have no capabilities;
- `UserRole` must not accumulate `can*` authorization methods;
- `User` must not duplicate role capability logic;
- privileged user-role changes require `manage-user-roles`;
- account activation changes require `manage-user-activation`;
- last-active-SuperAdmin protection remains a transactional business invariant;
- Spatie Permission is intentionally not adopted while roles/capabilities remain static.

## Privileged audit authority

Privileged/business audit is explicit and event-oriented. `spatie/laravel-activitylog:^5.0` owns storage/query infrastructure only.

Rules:
- do not attach broad `LogsActivity` automatic model-event logging across the domain;
- audit privileged mutations at the use-case/service boundary;
- record actor, subject when available, before/after state where meaningful, business rationale, and bounded context;
- never log passwords, remember tokens, two-factor secrets, recovery codes, API keys, provider credentials or raw sensitive payloads;
- existing domain-specific ledgers such as provider idempotency/audit records remain authoritative for their own invariants;
- Tinker must not be the normal interface for production user role/activation changes; use audited application commands.

## Contract-first repository safety

Before implementing changes that touch models, database schema, package-managed tables, runtime images or database-backed tests:

1. inspect the current repository model/migration/config/test authorities;
2. inspect the locked dependency/upstream contract when external packages are involved;
3. record or update the relevant machine-readable contract;
4. run repository static contracts before packaging;
5. use PostgreSQL test authority for all database-backed release tests;
6. require a clean schema before the full PostgreSQL suite;
7. treat `composer.lock` and `package-lock.json` as release artifacts;
8. canonical verification installs locked dependencies and must never update them.

A historical regression is not considered closed by a code patch alone; it must have a permanent machine-enforced guard recorded in the regression ledger.

## Required commands

```bash
php artisan songchart:doctor
composer repository-compiler:verify
composer verification-topology:verify
composer ai-protocol:verify
composer stage:verify
composer canonical:verify
```

## Forbidden shortcuts

- `--ignore-platform-reqs` to force package installation
- lowering PHPStan strictness to silence a local error
- regenerating a PHPStan baseline for new stage errors
- skipping PostgreSQL release tests for schema changes
- hard-coding privileged credentials in source/seeds
- package adoption without registry ownership
- publishing a candidate as fully verified when vendor/database/build gates were not actually run

## Verification consumer graph authority

Verification semantics are routed by `docs/project/engineering/verification-consumer-graph.json`.

Every verifier and Architecture test must have exactly one routing rule that declares:
- whether the consumer is declarative or behavioral;
- the machine semantic authorities it consumes;
- its execution ownership (for verifier scripts, resolved through Composer).

`RepositoryContractResolver` compiles this routing graph into `repository-contract-manifest.json`. Unowned consumers, overlapping ownership, unknown semantic authorities, missing execution owners, and registered cross-layer literal violations fail `repository-compiler:verify` before the PostgreSQL/Pest closure lane.

When an authority changes, the required workflow is authority → impact resolution → all registered consumers → focused verification → stage → canonical. Do not patch consumers one-by-one after canonical failures.

## Executable authority rule

Repository-wide invariants that have a registered authority must be resolved through `App\Support\Engineering\RepositoryContractResolver` or through a generated/approved execution adapter. Tests and verifiers must not copy authority-sensitive command strings or ordering assumptions into independent literals.

Before implementing a change to a registered authority or consumer, run the impact resolver. After authority changes, recompile and verify `docs/project/generated/repository-contract-manifest.json`.

Release-source packaging is post-canonical only. The package command must prove that the current source tree, authority graph and dependency lockfiles match canonical evidence.

## AI development protocol

AI/developer workflow authority is `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md` with machine-readable companion `docs/project/engineering/ai-development-contract.json`.

`AGENTS.md`, `CLAUDE.md` and `GEMINI.md` are thin bootstraps only. They must point to the protocol instead of copying implementation, verification or historical stage rules.

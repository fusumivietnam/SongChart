# Stage 22.0 — System Control Plane & Pre-Data Stabilization

Status: implementing.

## Purpose

Create one repository-native control plane over existing SongChart authorities and reduce expensive-to-reverse architecture/data risks before production data and public traffic make structural corrections costly.

This stage is governance and stabilization work. It must not redesign the domain merely because the database is still small.

## Outcomes

1. Central lifecycle/risk projection over existing authorities.
2. Repository-native roadmap that survives chat/AI handoff.
3. Explicit compatibility, migration and deprecation policy.
4. Pre-data freeze state and transition requirements.
5. Database risk inventory focused on identity, PK/FK/type, uniqueness, deletion/retention, polymorphic references and public URL durability.
6. Framework/runtime lifecycle where current versions are targets, not permanent architecture invariants.
7. Resilience hierarchy for GitHub/Codespaces/CI quota/external DB/local hardware constraints.
8. Machine verification that prevents silent drift between control-plane state and stack/runtime authorities.

## Non-goals

- no mass schema reset;
- no PK strategy change without evidence;
- no provider-specific canonical entities;
- no microservice split;
- no repository-pattern rollout;
- no package upgrade solely because a newer version exists;
- no weakening of canonical verification;
- no production data migration in this stage unless an accepted pre-data decision proves it necessary.

## Authority and official sources

### Repository authorities

Stage 22 consolidates and projects existing authorities; it does not supersede them.

- `PROJECT_AUTHORITY.md` — mandatory repository entry point, runtime baseline, delivery, database and application-boundary precedence.
- `docs/project/domain/product-user-journeys.json` — product/journey demand authority before schema or provider-driven expansion.
- `docs/project/domain/domain-contracts.json` and `DOMAIN_MODEL.md` — canonical entity/field/relationship semantics and change protocol.
- `docs/project/domain/IDENTIFIERS.md` — canonical ULID identity, provider/external identity separation and slug semantics.
- `docs/project/domain/DATA_CONTRACT.md` — cross-boundary values, dates, provider evidence, JSON and compatibility semantics.
- `docs/project/domain/schema-ownership.json` — table ownership authority.
- `docs/project/domain/application-data-boundary.json` — read/write persistence ownership and controller/application boundaries.
- `docs/project/stack/stack-manifest.json`, `FRAMEWORK_BASELINE.md`, `CAPABILITY_OWNERSHIP.md` and package registries — technology capability and current implementation authority.
- `docs/project/engineering/stage-plan.json` — active execution-stage/tranche authority.
- `docs/project/engineering/roadmap.json` — long-term direction only; it cannot activate a stage by itself.
- `docs/project/engineering/verification-topology.json`, `verification-consumer-graph.json`, `verification-command-surface.json` and repository contract compiler — verification ownership and authority-consumer routing.
- `docs/project/governance/CONTROL_PLANE.md` and `project-control-plane.json` — centralized lifecycle/risk projection over owning authorities.
- `docs/project/governance/COMPATIBILITY_POLICY.md` — additive/compatible/migration-required/breaking/destructive evolution policy.
- `docs/project/governance/pre-data-freeze.json` — pre-data lifecycle and transition gates.
- `docs/project/governance/resilience-matrix.json` — execution profile and GitHub/Codespaces/quota/hardware fallback policy.
- `docs/project/governance/technology-lifecycle.json` — current technology targets and replacement/upgrade boundaries.
- `docs/project/governance/database-risk-register.json` — pre-data persistence decision/risk inventory.
- `database/migrations`, `composer.lock` and `package-lock.json` — executable schema history and exact dependency evidence.

### Installed versions

Stage 22 does not introduce a replacement framework or package stack. Current approved targets remain:

- PHP `^8.5`.
- Laravel `^13.0`.
- PostgreSQL major `18` for release-authoritative database verification.
- Node.js major `24` as the current frontend/build runtime target.
- Livewire `^4.0` as the current bounded interaction implementation.
- Vite `^7`, Tailwind CSS `^4`, Pest `^4` and the repository-installed Laravel ecosystem packages.

Exact patch versions remain lockfile authority. Stage 22 explicitly classifies these values as lifecycle-managed current targets rather than permanent architecture invariants.

### Official external sources

No new third-party framework, database engine or CI platform is adopted by Stage 22. External sources are used only to validate lifecycle and fallback semantics already represented through repository-native contracts:

- Laravel release/support and upgrade documentation: `https://laravel.com/docs/releases` and the installed-major upgrade documentation.
- GitHub Actions self-hosted runner documentation: `https://docs.github.com/en/actions/concepts/runners/self-hosted-runners`.
- GitHub Codespaces billing/usage documentation: `https://docs.github.com/en/billing/concepts/product-billing/github-codespaces`.
- GitHub Actions billing/usage documentation: `https://docs.github.com/en/billing/concepts/product-billing/github-actions`.

These sources do not become SongChart architecture authority. They support operational decisions such as current support windows, quota constraints and runner fallback capabilities; repository contracts remain the executable authority.

### Native capability assessment

Stage 22 reuses existing SongChart/GitHub/platform capabilities before adding custom machinery:

- Existing `project-context`, `project-state` and repository contract compiler already generate/verify derived repository authority; the control plane extends this pattern instead of introducing a second state system.
- Existing `stack:verify` is already owned by `quality:verify`; Stage 22 adds lifecycle/control-plane checks there instead of creating a parallel closure command.
- Existing PostgreSQL snapshot, migration lifecycle, migration-upgrade and database-test lanes remain storage evidence owners.
- Existing impact resolver and verification topology remain responsible for focused versus closure verification.
- Existing Docker-first development plus Codespaces adapter already provide portable execution boundaries; Stage 22 formalizes Codespaces as optional rather than replacing them.
- GitHub-hosted Actions remains the primary CI surface while self-hosted/local execution is treated only as an evidence-compatible fallback under the same exact-SHA semantics.
- Composer/npm lockfiles and caches already provide reproducible dependency reconstruction; caches remain optimizations rather than authority.
- Existing domain contracts already separate canonical identity, provider evidence, read projections and mutation ownership; Stage 22 records lifecycle/risk around those decisions rather than redesigning them.

### Custom implementation justification

The custom Stage 22 files are required because the repository has strong specialist authorities but lacks one lifecycle/risk projection answering what exists, what is durable, what is replaceable, what should be upgraded/retired and which decisions must be closed before real data/traffic.

- `project-control-plane.json` is deliberately an index/projection, not a second domain/stack/schema authority.
- `roadmap.json` preserves long-term direction across AI/chat handoff while leaving stage activation exclusively to `stage-plan.json`.
- Compatibility and pre-data lifecycle contracts are needed because breaking changes have fundamentally different cost before and after durable production data exists.
- The resilience matrix is needed so Codespaces quota, GitHub-hosted runner quota, external DB availability or constrained developer hardware change execution location/profile without weakening required semantic verification.
- Technology lifecycle metadata prevents historical Stage literals such as a specific Node/PostgreSQL major from becoming accidental permanent architecture invariants.
- The database risk register surfaces expensive-to-reverse decisions before data growth without inventing migrations or resetting a schema that already has valid invariants.
- No custom authentication, queue runtime, package manager, database abstraction, CI platform or canonical identity system is introduced.

## Tranches

### 22.0A — Control Plane & Roadmap Authority

- `CONTROL_PLANE.md`
- `project-control-plane.json`
- repository-native `roadmap.json`
- common lifecycle/action/stability/risk/change classes
- decision-debt registry

### 22.0B — Pre-Data Architecture/Data Stabilization

- pre-data lifecycle state machine
- actual PostgreSQL schema inventory/snapshot evidence
- PK/FK/type/null/default/index/unique/delete behavior review
- deletion/retention matrix
- polymorphic referential-integrity policy and verifier
- public slug/redirect durability decision
- classify each reviewed item as keep/harden/change-before-data/compatibility/deprecated

### 22.0C — Framework/Runtime Lifecycle & Resilience

- current technology targets remain replaceable implementation metadata
- verifiers resolve Node/PostgreSQL target versions from authority instead of historical Stage literals
- framework upgrade protocol
- light/standard/full execution profiles
- GitHub-hosted/self-hosted/local exact-SHA verification fallback hierarchy
- Codespaces optional adapter, not continuity authority
- constrained-hardware policy

### 22.0D — Closure & Generated Views

- generated system status/upgrade radar/decision debt view
- control-plane verification in canonical quality path
- impact mapping for control-plane authorities
- exact-head canonical closure
- owner review of remaining R3/R4 decision debt before Stage 23 activation

## Invariants

- Existing owning authorities remain source authorities; the control plane is a projection/index, not a competing semantic owner.
- Canonical SongChart identity remains provider-neutral.
- Historical migrations that may have been applied are never rewritten for convenience.
- Search/read projections never become source of truth.
- New features target authoritative capability boundaries, not compatibility/deprecated shortcuts.
- Breaking durable changes become compatibility/migration work once the project is data-bearing.
- Quota, Codespaces availability or weak local hardware may change where verification runs, never what semantic evidence is required.
- A local/self-hosted fallback must be tied to exact Git SHA/tree and may not silently bypass owner merge gates.

## Owner decision gates

Owner input is required for:
- canonical identity/entity taxonomy changes;
- destructive/irreversible R4 changes;
- public URL durability policy if alternatives materially change product semantics;
- deletion/retention rules that intentionally discard audit/provenance/history;
- transition from `schema-baseline-approved` to `data-bearing`;
- final merge.

## Tests and verification

Stage 22 preserves all existing `quality:verify`, PostgreSQL, browser and frontend gates. Control-plane checks are added through already-owned verification surfaces rather than creating an unregistered parallel closure command.

Required evidence includes:

- `composer validate --strict`;
- repository/compiler and authority-consumer verification;
- `composer stack:verify` for control-plane/lifecycle coherence and current runtime target alignment;
- database/schema ownership and migration lifecycle checks;
- authoritative PostgreSQL tests and schema snapshot at closure;
- frontend production build and browser smoke through the existing Stage/canonical topology;
- exact-head canonical closure before promotion.

22.0A/C governance changes must not claim database behavior changes merely because database risk metadata was added. 22.0B may introduce a forward migration only if an accepted pre-data decision proves a persisted representation change is necessary.

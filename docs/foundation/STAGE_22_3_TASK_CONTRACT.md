# Stage 22.3 — AI-ready Control Plane

## Status

Active. Tranche `22.3A` owns the first bounded implementation slice.

Stage 22.2 is accepted through exact-head Auto Closure run 330. Stage 22.3 composes the existing deterministic repository, data, runtime and verification capabilities into machine-readable context. It does not add an external model, MCP server, autonomous production mutation, or a second repository authority.

## Goal

Expose one bounded, deterministic control plane that lets a human or coding agent understand current repository authority, data/runtime readiness, handoff state, next actions and required verification without reconstructing those facts from chat history.

## Tranches

### 22.3A — Unified project and engineering context

Status: active.

- Inventory existing `./songchart ai status --json`, generated project context, project intelligence and repository contract surfaces.
- Compose authored and generated authority into one bounded machine-readable engineering context.
- Preserve authored-source ownership and keep generated authority projection-only.
- Exclude secrets and volatile GitHub claims that must be resolved live.

### 22.3B — Data and runtime status composition

Status: planned.

- Compose existing doctor, provider, chart, storage/database and Recording data-trace capabilities.
- Emit explicit `ready`, `degraded`, `blocked` and `unknown` evidence from their owning capabilities.
- Avoid parallel health checks, duplicated provider logic, and inferred AI state.

### 22.3C — Bounded handoff and resume evidence

Status: planned.

- Produce deterministic branch, stage, task, changed-surface and verification context for device/agent handoff.
- Keep live branch/PR/head resolution outside authored progress.
- Preserve human ownership of promotion and production mutation.

### 22.3D — Machine-readable task and verification guidance

Status: planned.

- Derive bounded next-action and verification guidance from stage, impact and verification authorities.
- Keep recommendations traceable to repository-owned evidence.
- Close Stage 22.3 through exact-current-head Auto Closure.

## Acceptance criteria

- One machine-readable context surface composes existing owners rather than duplicating them.
- Every emitted field identifies its source authority or application capability.
- Missing, stale and blocked evidence remain explicit and fail closed.
- Output excludes secrets, credentials, raw sensitive provider payloads and unsupported volatile claims.
- Commands have declared mutation envelopes and non-interactive JSON behavior.
- Generated projections remain PREPARE-owned.
- Stage 22.3 closes only after exact-head PREPARE, QUALITY, PostgreSQL 18, browser, frontend and canonical CLOSE pass.

## Changed authorities

Initial stage activation changes or introduces:

- `docs/project/engineering/stage-plan.json`
- `docs/foundation/STAGE_22_3_TASK_CONTRACT.md`
- `candidate-verification.json`

Implementation changes must extend this list with every affected registered authority and reconcile all reverse verification consumers before closure.

## Affected modules and boundaries

Expected owners include:

- `./songchart ai ...` command routing;
- application/read-model composition for repository, data and runtime status;
- `docs/project/engineering/project-knowledge.json`;
- `docs/project/engineering/ai-development-contract.json`;
- `docs/project/engineering/verification-consumer-graph.json`;
- generated `development-state.json`, `project-context.json` and repository contract manifest projections;
- focused Feature and Architecture verification.

Canonical models, provider adapters and production write services remain unchanged unless a later tranche explicitly records and verifies that scope.

## Planned impact

- Run `./songchart impact <planned-paths...>` before each implementation slice.
- Run `./songchart impact --diff` after each source change.
- Reconcile registered authority dependents and contradiction scans before broad verification.
- Generated files may change only through `./songchart reconcile` or PREPARE.

## Command mutation envelopes

| Command surface | Envelope | Allowed tracked mutation | Interactive output |
|---|---|---|---|
| Control-plane context/status commands | read-only | none | no for `--json` |
| `./songchart reconcile` | generated-only | registered generated authority paths | no |
| Auto Closure PREPARE | generated-only | registered generated authority paths | no |
| Canonical CLOSE | closure | none on the verified tree | no |

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `PROJECT_AUTHORITY.md`
- `docs/project/generated/development-state.json`
- `docs/project/generated/project-context.json`
- `docs/project/engineering/project-knowledge.json`
- `docs/project/engineering/consolidation-plan.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/foundation/CODE_GENERATION_RULES.md`
- `docs/project/domain/application-data-boundary.json`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.5` | `composer.json` / `composer.lock` |
| Laravel | `^13.0` (resolved lockfile version) | `composer.json` / `composer.lock` |
| PostgreSQL | major 18 | repository runtime and verification authority |
| Node | 24 | repository runtime and lockfile authority |

Stage 22.3 adds no package or runtime dependency.

### Official external sources

Reviewed 2026-09-09:

- Laravel 13 Console application API: https://api.laravel.com/docs/13.x/Illuminate/Console/Application.html — existing Artisan command composition and non-interactive execution.
- Laravel 13 Command API: https://api.laravel.com/docs/13.x/Illuminate/Console/Command.html — command input/output boundaries.
- Laravel 13 Filesystem API: https://api.laravel.com/docs/13.x/Illuminate/Filesystem/Filesystem.html — bounded repository file reads and decoded JSON support where existing repository owners select it.

### Native capability assessment

Laravel Console, the existing SongChart command router, repository contract resolver, project intelligence, doctor/status commands and JSON authority files already provide the required primitives. Stage 22.3 should compose these owners through application/query boundaries. No external model runtime, MCP server, new framework or parallel control-plane store is required.

### Custom implementation justification

A narrow SongChart-specific composition layer is required because Laravel does not define SongChart stage authority, impact routing, provider/data readiness, exact-tree verification, or handoff semantics. Custom code must only map existing owners into a stable DTO/JSON contract and must not reproduce their business rules or persist a second source of truth.

## Domain contract and use-case data surface

- Actor: authenticated development operator or bounded coding agent using local/CI command surfaces.
- Inputs: repository-owned authority paths and explicit command options.
- Reads: authored and generated project authority, registered application status/query capabilities, and local Git state where explicitly owned.
- Writes: none for context/status commands.
- Unknown semantics: unavailable, stale or unverified inputs remain explicit; they are never promoted to ready.
- Output: versioned, deterministic JSON with source/provenance fields.
- Route/API contract: no public HTTP or production mutation route is introduced by default.
- Security: never emit credentials, secrets, private payloads, tokens, or environment values outside approved non-secret status fields.

## Security, authorization, and data impact

Control-plane reads remain local/CI operator capabilities. Production mutations, privileged business actions, PR promotion, merge and release remain human-governed. The stage must reuse existing redaction and application data boundaries and must not broaden provider credential access.

## Tests and verification

Focused evidence must cover:

- deterministic context schema and ordering;
- source-authority attribution;
- explicit unknown/degraded/blocked handling;
- secret and volatile-state exclusion;
- read-only command mutation boundaries;
- generated-authority ownership;
- handoff and verification guidance derived from registered authorities;
- existing command/help and AI protocol contracts.

Required closure:

```bash
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Exact-current-head Auto Closure must pass PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, failure classification, canonical CLOSE, exact-tree preservation and ready-to-promote before Stage 22.3 is accepted.

## Explicit non-goals

- External OpenAI, Anthropic or other paid-model calls.
- Local LLM/Ollama deployment.
- MCP server/runtime implementation.
- Autonomous production writes or self-healing.
- A second repository/project state database.
- New provider adoption.
- Public Stage 23 redesign or personalization.
- Microservices, Kubernetes, warehouse or lakehouse expansion.

## Documentation impact

Update the stage plan, current task contract and affected machine authorities as each tranche closes. Historical accepted-stage evidence remains immutable. Generated development/project context is refreshed only by its owning generator.

## Delivery and handoff

GitHub branch and PR state remain the live work lease. Resolve the exact branch, PR and head before writes. Handoff uses committed Git state plus bounded status/doctor evidence; local-only commits and chat-only progress are not authoritative.

## Rollback

Revert the bounded Stage 22.3 source/contract commit and regenerate projections through the owning reconciliation command. Do not hand-edit generated authority or accepted Stage 22.1/22.2 evidence.

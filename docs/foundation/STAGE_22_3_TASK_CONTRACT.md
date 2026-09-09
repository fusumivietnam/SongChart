# Stage 22.3 — AI-ready Control Plane

## Status

Active. Tranche `22.3A` is accepted on exact head `04b5ebcd1e7ad17de24b8cdef4d931a671c9cf9c` by Auto Closure run 344. Tranche `22.3B` is accepted on exact head `4ebf57fb6c06932f938c3917906e2bc7d0152387` by Auto Closure run 362. Tranche `22.3C` owns the current bounded implementation slice.

Stage 22.2 remains the latest accepted major stage. Stage 22.3 composes the existing deterministic repository, data, runtime and verification capabilities into machine-readable context. It does not add an external model, MCP server, autonomous production mutation, or a second repository authority.

## Goal

Expose one bounded, deterministic control plane that lets a human or coding agent understand current repository authority, data/runtime readiness, handoff state, next actions and required verification without reconstructing those facts from chat history.

## Tranches

### 22.3A — Unified project and engineering context

Status: accepted.

- Inventory existing `./songchart ai status --json`, generated project context, project intelligence and repository contract surfaces.
- Compose authored and generated authority into one bounded machine-readable engineering context.
- Preserve authored-source ownership and keep generated authority projection-only.
- Exclude secrets and volatile GitHub claims that must be resolved live.
- Exact-head evidence: Auto Closure run 344 on `04b5ebcd1e7ad17de24b8cdef4d931a671c9cf9c` passed PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, canonical CLOSE, exact-tree preservation and ready-to-promote.

### 22.3B — Data and runtime status composition

Status: accepted.

- Compose existing runtime owners through opt-in `./songchart ai status --json --runtime`.
- Keep default `./songchart ai status --json` repository-only and non-probing.
- Evaluate `songchart:doctor --strict`, development database status and development storage status through their existing command owners.
- Report a Recording data trace as `requires_subject` until an explicit Recording is supplied; never invent a canonical subject.
- Runtime probes are read-only, bounded by timeout, output-redacted and fail closed on non-zero exit or timeout.
- Do not execute deep diagnostics, impact verification, candidate verification or canonical verification as implicit status probes.
- Emit only bounded status evidence (`ready`/`blocked`/`requires_subject`), owner, exit code/timeout state and secret-exclusion markers; raw stdout/stderr is not part of the control-plane response.
- Exact-head evidence: Auto Closure run 362 on `4ebf57fb6c06932f938c3917906e2bc7d0152387` passed PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, failure classification, canonical CLOSE, exact-tree preservation and ready-to-promote.

### 22.3C — Bounded handoff and resume evidence

Status: active.

- Produce deterministic branch, stage, active-tranche, task-contract and local changed-surface evidence for device/agent handoff.
- Keep committed PR change-surface and workflow verdict as explicit live-GitHub resolutions instead of copying volatile claims into repository-authored progress.
- Expose upstream/ahead/behind state when Git can resolve it locally; unknown facts remain explicit rather than inferred.
- Bound path lists, sort them deterministically, and emit counts/truncation state.
- Preserve human ownership of promotion and production mutation.
- Never include secret values, environment contents, command output, or chat memory as handoff authority.

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
- Runtime status is opt-in, read-only and bounded; default orientation does not start environment-specific probes.
- Handoff identifies the current Git head, stage, active tranche, task contract and bounded local change surface without claiming live PR/workflow facts it has not resolved.
- Commands have declared mutation envelopes and non-interactive JSON behavior.
- Generated projections remain PREPARE-owned.
- Stage 22.3 closes only after exact-head PREPARE, QUALITY, PostgreSQL 18, browser, frontend and canonical CLOSE pass.

## Changed authorities

None. Stage 22.3C extends the runtime handoff projection of existing Git and stage-plan owners but does not create or modify a registered semantic authority. The stage plan and this task contract remain stage-control metadata rather than entries in the semantic authority dependency registry.

Implementation scope is recorded below under affected modules and boundaries. Git remains the local repository-state owner, GitHub PR/workflow state remains live work-lease evidence, and generated repository authority remains PREPARE-owned.

## Affected modules and boundaries

Expected implementation owners include the existing AI status/project-state composition surface and focused control-plane tests. Existing runtime diagnostic commands retain ownership of their checks.

Related repository surfaces include:

- `./songchart ai ...` command routing;
- `scripts/project-state.php` handoff projection;
- local Git branch/head/upstream/change-surface reads;
- `docs/project/engineering/stage-plan.json` for current stage/tranche/task intent;
- generated `development-state.json` and `project-context.json` projections;
- focused Feature, Unit and Architecture verification.

Canonical models, provider adapters and production write services remain unchanged.

## Planned impact

- Run `./songchart impact <planned-paths...>` before each implementation slice.
- Run `./songchart impact --diff` after each source change.
- Reconcile registered authority dependents and contradiction scans before broad verification.
- Generated files may change only through `./songchart reconcile` or PREPARE.

## Command mutation envelopes

| Command surface | Envelope | Allowed tracked mutation | Interactive output |
|---|---|---|---|
| `./songchart ai status --json` | read-only repository orientation + bounded handoff evidence | none | no |
| `./songchart ai status --json --runtime` | read-only bounded runtime probes | none | no |
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
- Symfony Process component — existing application dependency used for bounded subprocess execution; Stage 22.3B exposes only exit/timeout state and never process output through the control-plane response.
- Git CLI — existing repository-state owner for branch, head, working-tree and upstream evidence. Stage 22.3C does not replace Git with a second state store.

### Native capability assessment

Laravel Console, Symfony Process, Git, the existing SongChart command router, repository contract resolver, project intelligence, doctor/status commands and JSON authority files already provide the required primitives. Stage 22.3 composes these owners through narrow control-plane boundaries. No external model runtime, MCP server, new framework or parallel control-plane store is required.

### Custom implementation justification

A narrow SongChart-specific composition layer is required because Laravel and Git do not define SongChart stage authority, impact routing, runtime readiness, exact-tree verification or handoff semantics. Custom code only maps existing owners into a stable JSON contract and must not reproduce their business rules or persist a second source of truth.

## Domain contract and use-case data surface

- Actor: authenticated development operator or bounded coding agent using local/CI command surfaces.
- Inputs: repository-owned authority paths and explicit command options.
- Reads: authored and generated project authority, registered application status/query capabilities, and local Git/runtime state where explicitly owned.
- Writes: none for context/status commands.
- Unknown semantics: unavailable, stale or unverified inputs remain explicit; they are never promoted to ready.
- Output: versioned, deterministic JSON with source/provenance fields.
- Handoff: local Git facts are emitted directly; committed PR change-surface and workflow verdict remain `requires_live_*_resolution` until resolved from GitHub.
- Route/API contract: no public HTTP or production mutation route is introduced by default.
- Security: never emit credentials, secrets, private payloads, tokens, raw command output or environment values outside approved non-secret status fields.

## Security, authorization, and data impact

Control-plane reads remain local/CI operator capabilities. Production mutations, privileged business actions, PR promotion, merge and release remain human-governed. The stage reuses existing redaction and application data boundaries and does not broaden provider credential access.

## Tests and verification

Focused evidence must cover:

- deterministic context schema and ordering;
- source-authority attribution;
- default status remains non-probing;
- runtime success, non-zero failure, timeout and unavailable executable handling;
- explicit `requires_subject` handling for data trace;
- secret/raw-output exclusion;
- branch/head/stage/tranche/task handoff evidence;
- bounded deterministic local changed-surface projection;
- explicit live-PR and live-workflow resolution requirements;
- read-only command mutation boundaries;
- generated-authority ownership;
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

GitHub branch and PR state remain the live work lease. Resolve the exact branch, PR and head before writes. Local handoff may expose Git branch/head/upstream and bounded working-tree paths, but it must not claim committed PR change-surface or workflow verdict without live GitHub resolution. Chat-only progress is not authoritative.

## Rollback

Revert the bounded Stage 22.3 source/contract commit and regenerate projections through the owning reconciliation command. Do not hand-edit generated authority or accepted Stage 22.1/22.2 evidence.

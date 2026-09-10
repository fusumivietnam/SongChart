# Stage 22.3 — AI-ready Control Plane

## Status

Active. Tranches 22.3A, 22.3B and 22.3C are accepted. Tranche 22.3D owns the current bounded implementation and major-stage closure slice.

Stage 22.2 remains the latest accepted major stage until Stage 22.3 exact-head closure passes. Stage 22.3 composes deterministic repository, data, runtime, handoff and verification capabilities into one machine-readable control plane. It does not add an external model, MCP server, autonomous production mutation, or a second repository authority.

## Goal

Expose one bounded, deterministic control plane that lets a human or coding agent understand current repository authority, data/runtime readiness, handoff state, next actions and required verification without reconstructing those facts from chat history.

## Tranches

### 22.3A — Unified project and engineering context

Status: accepted.

- Compose authored and generated repository authority into one bounded machine-readable engineering context.
- Preserve authored-source ownership and generated projection-only semantics.
- Exact-head evidence: Auto Closure run 344 on `04b5ebcd1e7ad17de24b8cdef4d931a671c9cf9c` passed PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, canonical CLOSE, exact-tree preservation and ready-to-promote.

### 22.3B — Data and runtime status composition

Status: accepted.

- Compose existing runtime owners through opt-in `./songchart ai status --json --runtime`.
- Keep default status repository-only and non-probing.
- Runtime probes remain read-only, bounded, secret-safe and fail closed.
- Exact-head evidence: Auto Closure run 362 on `4ebf57fb6c06932f938c3917906e2bc7d0152387` passed PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, failure classification, canonical CLOSE, exact-tree preservation and ready-to-promote.

### 22.3C — Bounded handoff and resume evidence

Status: accepted.

- Produce deterministic branch, stage, active-tranche, task-contract and local changed-surface evidence for device/agent handoff.
- Keep committed PR change-surface and workflow verdict as explicit live-GitHub resolutions.
- Bound and sort local path evidence; exclude secrets, raw command output and chat memory.
- Exact-head evidence: Auto Closure run 370 on `c5c910aa1f3607f80732ef9d10961da26f771a8f` passed PREPARE/QUALITY, PostgreSQL 18, hardened Playwright browser smoke, frontend build, failure classification, canonical CLOSE, exact-tree preservation and ready-to-promote.

### 22.3D — Machine-readable task and verification guidance

Status: active.

- Derive active goals from the authored stage plan.
- Derive impact owner, commands and rules from verification topology.
- Derive focused, candidate and canonical public entrypoints from verification command-surface authority.
- Emit bounded next actions with source provenance and explicit no-mutation semantics.
- Preserve human ownership of repository writes, promotion, merge and production mutation.
- Close Stage 22.3 through exact-current-head Auto Closure.

## Acceptance criteria

- One machine-readable status surface composes existing owners rather than duplicating their business or verification logic.
- Every emitted guidance group identifies its repository source authority.
- Missing, stale and blocked evidence remain explicit and fail closed.
- Output excludes secrets, credentials, raw sensitive provider payloads and unsupported volatile claims.
- Runtime status is opt-in, read-only and bounded.
- Handoff identifies current Git/stage context without inventing live PR/workflow verdicts.
- Guidance uses the current stage plan plus registered verification topology and public command surface instead of hard-coded parallel policy.
- Guidance never authorizes a repository or production mutation.
- Generated projections remain PREPARE-owned.
- Stage 22.3 closes only after exact-head PREPARE, QUALITY, PostgreSQL 18, browser, frontend, failure classification, canonical CLOSE, exact-tree preservation and ready-to-promote pass.

## Changed authorities

None.

Stage 22.3D changes consumers of existing stage and verification authorities. It does not register a new semantic authority. Stage-control metadata remains owned by the stage plan and this task contract; generated repository authority remains PREPARE-owned.

## Affected modules and boundaries

Implementation owners include:

- `./songchart ai status --json` routing;
- bounded handoff composition;
- bounded guidance composition;
- opt-in runtime status composition;
- focused AI control-plane Feature tests.

Read-only source authorities consumed by 22.3D include the authored stage plan, verification topology and verification command-surface contract. Canonical models, provider adapters, production write services and public HTTP routes remain unchanged.

## Planned impact

- Resolve planned impact before implementation using the existing impact owner.
- Resolve actual diff after source changes.
- Run only registered focused checks during implementation.
- Use candidate/canonical owners for closure; do not reproduce their assertions in the guidance composer.
- Generated files may change only through governed reconcile/PREPARE flows.

## Command mutation envelopes

| Command surface | Envelope | Allowed tracked mutation | Interactive output |
|---|---|---|---|
| `./songchart ai status --json` | read-only repository orientation + handoff + guidance | none | no |
| `./songchart ai status --json --runtime` | read-only orientation + handoff + guidance + bounded runtime probes | none | no |
| `./songchart reconcile` | generated-only | registered generated authority paths | no |
| Auto Closure PREPARE | generated-only | registered generated authority paths | no |
| Canonical CLOSE | closure | none on verified tree | no |

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/generated/development-state.json`
- `docs/project/generated/project-context.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/foundation/CODE_GENERATION_RULES.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.5` | `composer.json` / `composer.lock` |
| Laravel | `^13.0` | `composer.json` / `composer.lock` |
| PostgreSQL | major 18 | repository runtime and verification authority |
| Node | 24 | repository runtime and lockfile authority |

Stage 22.3 adds no package or runtime dependency.

### Official external sources

Reviewed 2026-09-09:

- Laravel 13 Console application API — existing command composition and non-interactive execution.
- Laravel 13 Command API — command input/output boundaries.
- Symfony Process component — existing bounded subprocess execution dependency.
- Git CLI — existing repository-state owner for branch, head, working-tree and upstream evidence.

### Native capability assessment

Laravel Console, Symfony Process, Git, the SongChart command router, stage plan, verification topology, verification command surface, repository project-state compiler, doctor/status commands and GitHub exact-head closure already provide all required primitives. Stage 22.3D composes these owners and does not require an external model runtime, MCP server, new framework or parallel state store.

### Custom implementation justification

A narrow SongChart-specific guidance projection is required because generic framework tooling does not understand SongChart stage goals, impact ownership, public verification entrypoints or exact-head acceptance policy. The custom layer only maps existing authorities into bounded JSON; it must not execute writes, redefine verification logic or persist a second source of truth.

## Domain contract and use-case data surface

- Actor: development operator or bounded coding agent using local/CI command surfaces.
- Inputs: repository-owned authority paths plus explicit status options.
- Reads: authored/generated project authority, local Git state and explicitly requested runtime readiness owners.
- Writes: none for context/status/guidance commands.
- Unknown semantics: unavailable, stale or unverified inputs remain explicit.
- Output: deterministic JSON with source/provenance fields and human-gated write boundaries.
- Route/API contract: no public HTTP or production mutation route is introduced.
- Security: never emit credentials, tokens, raw provider payloads, environment secret values or raw diagnostic command output.

## Security, authorization, and data impact

Control-plane reads remain development/CI operator capabilities. Production mutations, privileged business actions, PR promotion, merge and release remain human-governed. Stage 22.3D broadens no provider credential access and creates no production data mutation path.

## Tests and verification

Focused evidence must cover:

- deterministic context and handoff schema;
- active tranche/task contract projection;
- default status remains non-probing;
- runtime success/failure/timeout/unavailable handling;
- secret/raw-output exclusion;
- authority-backed next actions;
- verification owner and public-entrypoint provenance;
- all emitted recommended actions remain non-mutating/human-gated;
- generated-authority ownership and existing AI protocol contracts.

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
- Autonomous repository or production writes.
- A second repository/project state database.
- New provider adoption.
- Stage 23 UI redesign or personalization.
- Microservices, Kubernetes, warehouse or lakehouse expansion.

## Documentation impact

Update stage-control metadata as tranche/major-stage acceptance changes. Generated development/project context remains generator-owned and is refreshed only by governed PREPARE/reconcile flows.

## Delivery and handoff

GitHub branch and PR state remain the live work lease. Resolve exact branch, PR, head and workflow before writes or acceptance claims. Machine guidance may recommend read-only impact/verification entrypoints but cannot claim a live workflow verdict without GitHub resolution.

## Rollback

Revert the bounded Stage 22.3D source/contract commits and regenerate projections through their owning reconciliation flow. Do not hand-edit generated authority or historical accepted-stage evidence.

# Stage 22.4 — Vibe Coding Operations

## Status

Active. Tranches 22.4A, 22.4B and 22.4C are accepted; tranche 22.4D owns the current bounded implementation. 22.4A closed through SongChart Auto Closure run 384. 22.4B closed through Auto Closure run 391 on exact source head `3f74d596f151567dec763bf1fdb75b3b66d708ce`. 22.4C closed through Auto Closure run 397 on exact source head `c3a3842534c080bc61fbe70882ec0262b5170a2c`; PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, exact-head classification, canonical CLOSE, exact-tree preservation, final exact-head revalidation and ready-to-promote all passed.

## Goal

Use the accepted Stage 22.3 deterministic AI control plane to reduce manual reconstruction during coding, verification and handoff. Stage 22.4 composes existing repository authorities into task-oriented operations; it does not introduce a second repository authority, autonomous production mutation, a second verifier, or model-specific orchestration.

## Tranches

### 22.4A — Task-oriented coding operation bundles

Status: accepted.

- Compose existing orientation, impact, focused verification and closure surfaces into bounded machine-readable bundles.
- Derive commands and provenance from authored stage and verification authorities rather than hard-coding a parallel workflow.
- Keep every emitted action non-mutating by default and explicit about human gates.
- Reuse `./songchart ai status --json` as the machine-readable surface rather than creating a competing CLI.

### 22.4B — Impact-aware verification recommendations

Status: accepted.

- Map the resolved actual repository change surface to the existing impact resolver output.
- Expose the resolved focused-check evidence without independently executing child checks.
- Recommend only the registered `songchart impact --verify` public entrypoint and leave overlap collapse/execution to `scripts/run-impact-verification.sh`.
- Preserve resolver, impact-map, execution-owner and human-gate provenance.

### 22.4C — Bounded agent handoff and resume workflows

Status: accepted.

- Convert deterministic handoff evidence into an explicit, machine-readable resume workflow.
- Reuse handoff branch/head/working-tree facts, authored stage/tranche/task-contract authority and registered read-only command surfaces.
- Surface live PR and workflow resolution as required runtime steps instead of persisting volatile GitHub facts.
- Mark continuity as blocked/degraded when repository state is not safe to resume rather than inventing a prior-session state.
- Keep chat memory advisory only and all repository writes human-gated.

### 22.4D — Human-gated repository operation automation

Status: active.

- Compose a machine-readable repository operation plan from the accepted operation bundles and resume workflow.
- Keep read-only orientation, implementation guidance, verification and closure phases delegated to their existing command owners.
- Represent source change, commit/push, PR promotion, merge, release and production mutation as explicit human-gated intents with no autonomous command execution.
- Require `execution_allowed=false`, `auto_execute=false` and explicit human approval for every write-capable action.
- Close Stage 22.4 through exact-current-head Auto Closure before advancing to Stage 23.

## Acceptance criteria

- The AI status JSON exposes task-oriented operation bundles from existing repository authorities.
- The AI status JSON exposes bounded impact-aware verification evidence for the actual diff when a diff exists.
- Resolved checks come from `scripts/resolve-repository-impact.php`; no second impact matcher is introduced.
- The only focused execution recommendation is the registered `songchart impact --verify` entrypoint; child-check collapse remains owned by `scripts/run-impact-verification.sh`.
- The AI status JSON exposes a bounded `resume_workflow` composed from deterministic handoff evidence, authored stage authority and registered read-only command surfaces.
- Resume workflow state never treats chat memory as authority and never persists live PR/workflow facts into authored progress.
- Resume workflow requires live PR and exact-head workflow resolution before repository mutation or acceptance claims.
- The AI status JSON exposes a projection-only `repository_operation_plan` composed from existing operation bundles and resume workflow.
- Every write-capable operation requires explicit human approval, exposes no executable write command, and has autonomous execution disabled.
- Source modification, commit/push, PR promotion, merge, release and production mutation remain outside autonomous AI execution.
- Dirty, divergent or unresolved repository continuity is surfaced explicitly rather than silently accepted.
- No bundle, recommendation, resume workflow or operation plan grants autonomous repository or production mutation.
- Missing stage goals or required verification authorities fail closed; absence of a change surface is reported as not applicable rather than fabricated.
- Existing Stage 22.3 context/runtime/handoff/guidance and Stage 22.4A–22.4C outputs remain compatible.
- Focused regression coverage proves provenance, deduplication ownership, resume boundaries, operation write gates and secret exclusion.
- Generated projections remain PREPARE-owned.
- Each accepted tranche requires exact-head Auto Closure evidence before stage authority advances.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/generated/project-context.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`

### Installed versions

Stage 22.4 introduces no package or runtime dependency. Exact package versions remain owned by `composer.lock` and `package-lock.json`. The accepted runtime baseline remains PHP 8.5, Laravel 13, PostgreSQL 18, Node 24 and the repository-owned Redis/Docker development topology.

### Official external sources

No new external API, package, provider schema or model runtime is introduced by Stage 22.4. The stage intentionally reuses already accepted Laravel Console, Symfony Process and Git command/runtime capabilities. No external source is allowed to override SongChart repository authority.

### Native capability assessment

Laravel Console, Symfony Process, Git, `./songchart`, Stage 22.3 control-plane scripts, `scripts/resolve-repository-impact.php`, `scripts/run-impact-verification.sh`, the impact-test map and verification topology/command-surface contracts already provide the primitives required for Stage 22.4. A new CLI framework, agent framework, workflow engine, impact matcher, handoff database, write executor or verifier would duplicate accepted ownership and is rejected.

### Custom implementation justification

A narrow SongChart-specific projection is required because generic tooling does not understand SongChart stage goals, semantic impact ownership, public verification entrypoints, exact-head acceptance rules or human write gates. Stage 22.4 only composes bounded JSON around existing owners. It must not execute writes, redefine verification logic, store a second source of truth or persist volatile GitHub state.

## Command mutation envelopes

| Surface | Envelope | Tracked mutation |
|---|---|---|
| `./songchart ai status --json` | read-only orientation, handoff, guidance, operation bundles, impact recommendations, resume workflow and repository operation plan | none |
| `./songchart ai status --json --runtime` | read-only status plus bounded runtime probes | none |
| `./songchart impact --diff` | read-only actual-diff resolution | none |
| `./songchart impact --verify` | focused verification orchestration | none expected from the verified source tree |
| `./songchart candidate` | candidate closure | governed by existing owner |
| `./songchart verify` | canonical closure | none on verified tree |
| repository writes / PR promotion / merge / release / production mutation | human-governed | explicit human approval required |

## 22.4A implementation boundary

The operation-bundle projection covers `orient`, `implement`, `verify` and `close`. Every bundle identifies provenance, has `mutation_allowed=false`, requires a human gate for writes and disables autonomous execution.

## 22.4B implementation boundary

Impact-aware guidance may invoke the existing resolver in read-only `--diff --json` mode and project only bounded evidence: change count, matched rule names, impacted authority names, resolved focused checks and the registered focused execution entrypoint. It must not copy path-pattern matching logic from the impact map or copy check-collapse semantics from the impact runner.

If the resolver cannot establish an actual change surface, the recommendation is `not_applicable`. If a change surface resolves but the registered focused public entrypoint is absent, guidance fails closed as `blocked`.

## 22.4C implementation boundary

The resume workflow must be projection-only. It consumes the existing `control_plane.handoff` object plus authored stage authority and registered development/focused/closure entrypoints. It may describe read-only resume steps such as orientation, live PR resolution, exact-head workflow resolution and impact verification, but must not perform network mutation, source mutation, merge, release or production writes.

Live PR and workflow values remain runtime-resolved facts. The authored stage plan may record accepted exact-head evidence only after closure; it must not become a cache of volatile current PR state. Chat history and model memory are never continuity authority.

## 22.4D implementation boundary

The repository operation plan is projection-only and composes `control_plane.operation_bundles` with `control_plane.resume_workflow`. Existing bundle owners remain authoritative for read-only commands. The operation-plan projection must not introduce write commands, call GitHub mutation APIs, invoke Git writes, merge, release, deploy or mutate production.

Write-capable intents are limited to describing source change, commit/push, PR promotion, merge, release and production mutation boundaries. Every such intent must require explicit human approval and remain non-executable in the projection. Missing operation bundles or unsafe resume continuity fail closed.

## Tests and verification

Focused evidence must prove:

- operation bundles remain deterministic and non-mutating;
- active stage/tranche context comes from the authored stage plan;
- impact evidence comes from the existing actual-diff resolver;
- the recommendation delegates execution to `songchart impact --verify` / `scripts/run-impact-verification.sh` instead of exposing a duplicate execution plan;
- resume workflow branch/head/stage/tranche/task-contract facts are inherited from existing handoff/stage authority;
- live PR/workflow resolution remains explicitly required and is not fabricated or persisted as authored state;
- repository operation plan phases inherit existing operation bundles rather than defining a second command registry;
- all write-capable operation intents require human approval and expose no autonomous executable command;
- resolver/impact-map/execution-owner provenance is explicit;
- secrets and raw sensitive command output are excluded;
- Stage 22.3 and Stage 22.4A–22.4C control-plane fields remain present.

Required closure remains owned by existing entrypoints:

```bash
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Exact-current-head Auto Closure must pass before tranche and Stage 22.4 acceptance.

## Explicit non-goals

- Autonomous source modification.
- Automatic commit or push.
- Automatic PR promotion, merge or release.
- Production mutation.
- External model/API dependency.
- New MCP runtime.
- Duplicate impact matcher, verifier or test harness.
- Independent execution of resolver child checks from the AI projection.
- Persisted chat/session memory as repository continuity authority.
- Persisted volatile PR/workflow state in authored stage authority.
- New provider, chart or canonical identity semantics.
- Stage 23 public UX work.

## Delivery and handoff

Continue the existing Stage 22 umbrella branch and PR. Resolve exact branch, PR head and workflow state live before acceptance claims. Generated authority is refreshed only by the owning PREPARE/reconcile flow.

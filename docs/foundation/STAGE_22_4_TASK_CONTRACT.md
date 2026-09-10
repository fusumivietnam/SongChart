# Stage 22.4 — Vibe Coding Operations

## Status

Active. Tranche 22.4A owns the current bounded implementation. Stage 22.3 is accepted on exact head `bc17d5e0d05a2f1d40ed7ebbc124bc837dbd6c39` after SongChart Auto Closure run 377 passed PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, exact-head failure classification, canonical CLOSE, exact-tree preservation, final exact-head revalidation and ready-to-promote.

## Goal

Use the accepted Stage 22.3 deterministic AI control plane to reduce manual reconstruction during coding, verification and handoff. Stage 22.4 composes existing repository authorities into task-oriented operations; it does not introduce a second repository authority, autonomous production mutation, a second verifier, or model-specific orchestration.

## Tranches

### 22.4A — Task-oriented coding operation bundles

Status: active.

- Compose existing orientation, impact, focused verification and closure surfaces into bounded machine-readable bundles.
- Derive commands and provenance from the authored stage plan, verification topology and verification command-surface authority.
- Keep every emitted action non-mutating by default and explicit about human gates.
- Reuse `./songchart ai status --json` as the machine-readable surface rather than creating a competing CLI.

### 22.4B — Impact-aware verification recommendations

Status: planned.

- Map resolved repository change surfaces to the smallest registered verification set.
- Reuse the existing impact resolver and verification topology.
- Do not duplicate test selection or closure semantics.

### 22.4C — Bounded agent handoff and resume workflows

Status: planned.

- Convert deterministic handoff evidence into explicit resume workflows.
- Keep live PR/workflow facts as runtime resolutions rather than persisted authored state.
- Keep chat memory advisory only.

### 22.4D — Human-gated repository operation automation

Status: planned.

- Compose safe repository operation plans over existing command owners.
- Require explicit human approval before write-capable execution.
- Close Stage 22.4 through exact-current-head Auto Closure.

## Acceptance criteria

- The AI status JSON exposes task-oriented operation bundles from existing repository authorities.
- Bundles identify their source authority and contain only registered public command entrypoints or descriptive human-gated steps.
- No bundle grants autonomous repository or production mutation.
- Missing stage goals or verification authorities fail closed.
- Existing Stage 22.3 context, runtime, handoff and guidance outputs remain compatible.
- Focused regression coverage proves deterministic bundle composition and secret exclusion.
- Generated projections remain PREPARE-owned.
- Each accepted tranche requires exact-head Auto Closure evidence before stage authority advances.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/generated/project-context.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`

### Installed versions

Stage 22.4 introduces no package or runtime dependency. Exact package versions remain owned by `composer.lock` and `package-lock.json`. The accepted runtime baseline remains PHP 8.5, Laravel 13, PostgreSQL 18, Node 24 and the repository-owned Redis/Docker development topology.

### Official external sources

No new external API, package, provider schema or model runtime is introduced by Stage 22.4A. The tranche intentionally reuses the already accepted Laravel Console, Symfony Process and Git command/runtime capabilities documented by Stage 22.3. No external source is allowed to override SongChart repository authority.

### Native capability assessment

Laravel Console, Symfony Process, Git, `./songchart`, the Stage 22.3 AI control-plane scripts, the impact resolver and the verification topology/command-surface contracts already provide the primitives required for task-oriented operation bundles. A new CLI framework, agent framework, workflow engine or verifier would duplicate accepted ownership and is therefore rejected.

### Custom implementation justification

A narrow SongChart-specific projection is required because generic framework tooling does not understand SongChart stage goals, semantic impact ownership, public verification entrypoints or exact-head acceptance rules. The custom implementation only composes existing authorities into bounded JSON and must not execute writes, redefine verification logic, store a second source of truth or persist volatile GitHub state.

## Command mutation envelopes

| Surface | Envelope | Tracked mutation |
|---|---|---|
| `./songchart ai status --json` | read-only orientation, handoff, guidance and operation bundles | none |
| `./songchart ai status --json --runtime` | read-only status plus bounded runtime probes | none |
| `./songchart impact --diff` | read-only actual-diff resolution | none |
| `./songchart impact --verify` | focused verification orchestration | none expected from the verified source tree |
| `./songchart candidate` | candidate closure | governed by existing owner |
| `./songchart verify` | canonical closure | none on verified tree |
| repository writes / PR promotion / merge / production mutation | human-governed | explicit human approval required |

## 22.4A implementation boundary

Implementation may change only the narrow AI control-plane composition and focused tests required to expose operation bundles. It must not change canonical models, providers, chart semantics, public routes, schema, authentication, authorization or production mutation services.

The operation-bundle projection must cover at least:

1. `orient` — inspect deterministic project/stage/handoff state;
2. `implement` — resolve planned/actual impact and identify the current stage goal without executing a write;
3. `verify` — expose registered focused verification entrypoints;
4. `close` — expose candidate/canonical closure entrypoints and the exact-head GitHub evidence rule.

All bundles must include provenance and a `human_gate_required_for_writes` boundary.

## Tests and verification

Focused evidence must prove:

- operation bundles are deterministic arrays/objects on the JSON status surface;
- commands come from registered verification/public command authorities;
- current stage/tranche context is projected from the authored stage plan;
- every bundle remains non-mutating unless an explicit future human-gated execution surface is accepted;
- secrets and raw sensitive command output are not included;
- Stage 22.3 control-plane fields remain present.

Required closure remains owned by existing entrypoints:

```bash
./songchart impact --diff
./songchart impact --verify
./songchart candidate
./songchart verify
```

Exact-current-head Auto Closure must pass before tranche acceptance.

## Explicit non-goals

- Autonomous source modification.
- Automatic merge or release.
- Production mutation.
- External model/API dependency.
- New MCP runtime.
- Duplicate verifier/test harness.
- New provider, chart or canonical identity semantics.
- Stage 23 public UX work.

## Delivery and handoff

Continue the existing Stage 22 umbrella branch and PR. Resolve exact branch, PR head and workflow state live before acceptance claims. Generated authority is refreshed only by the owning PREPARE/reconcile flow.

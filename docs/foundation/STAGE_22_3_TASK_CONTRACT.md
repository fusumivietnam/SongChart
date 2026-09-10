# Stage 22.3 — AI-ready Control Plane

## Status

Accepted. Tranches 22.3A through 22.3D are accepted. Stage 22.3 closed on exact head `bc17d5e0d05a2f1d40ed7ebbc124bc837dbd6c39`; SongChart Auto Closure run 377 (`34421739826`) passed PREPARE/QUALITY, PostgreSQL 18, browser smoke, frontend build, exact-head failure classification, canonical CLOSE, exact-tree preservation, final exact-head revalidation and ready-to-promote. Repository-owner acceptance was given on 2026-09-10 after that evidence was reviewed.

## Goal delivered

Stage 22.3 composes deterministic repository, data, runtime, handoff and verification capabilities into one machine-readable control plane so a human or coding agent can orient and resume work without reconstructing volatile facts from chat history.

It does not add an external model, MCP server, autonomous production mutation or a second repository authority.

## Accepted tranches

### 22.3A — Unified project and engineering context

Accepted on exact head `04b5ebcd1e7ad17de24b8cdef4d931a671c9cf9c` by Auto Closure run 344. Authored and generated repository authority are composed through one bounded machine-readable engineering context while source ownership remains intact.

### 22.3B — Data and runtime status composition

Accepted on exact head `4ebf57fb6c06932f938c3917906e2bc7d0152387` by Auto Closure run 362. Existing doctor/provider/chart/storage/database/data-trace owners are composed through opt-in, read-only, bounded runtime probing with explicit ready/degraded/blocked evidence.

### 22.3C — Bounded handoff and resume evidence

Accepted on exact head `c5c910aa1f3607f80732ef9d10961da26f771a8f` by Auto Closure run 370. Branch/stage/task/local-change evidence survives device or agent handoff without persisting secrets or inventing live GitHub state.

### 22.3D — Machine-readable task and verification guidance

Accepted on exact head `bc17d5e0d05a2f1d40ed7ebbc124bc837dbd6c39` by Auto Closure run 377. Active goals, impact owner, focused/candidate/canonical public entrypoints and bounded next actions are derived from existing stage and verification authorities. Every emitted action remains non-mutating and preserves human ownership of writes, promotion, merge and production mutation.

## Accepted invariants

- `./songchart ai status --json` is the machine-readable repository orientation, handoff and guidance surface.
- `./songchart ai status --json --runtime` adds bounded runtime probes only when explicitly requested.
- Stage goals come from `docs/project/engineering/stage-plan.json`.
- Verification ownership comes from `verification-topology.json` and `verification-command-surface.json`; the control plane does not duplicate verification logic.
- Missing/stale/unresolved evidence is explicit and fails closed.
- Secrets, credentials, raw sensitive provider payloads and raw diagnostic command output are excluded.
- Generated projections remain PREPARE-owned.
- Live PR/workflow verdicts are resolved from GitHub at session/acceptance time, not stored as mutable authored state.
- Repository writes, PR promotion, merge, release and production mutation remain human-governed.

## Authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/stage-plan.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/generated/development-state.json`
- `docs/project/generated/project-context.json`

## Command mutation envelopes

| Surface | Envelope | Tracked mutation |
|---|---|---|
| `./songchart ai status --json` | read-only orientation + handoff + guidance | none |
| `./songchart ai status --json --runtime` | read-only status + bounded runtime probes | none |
| `./songchart reconcile` | generated-only | registered generated authority paths |
| Auto Closure PREPARE | generated-only | registered generated authority paths |
| Canonical CLOSE | closure | none on verified tree |

## Closure evidence

Final closure owner remained the existing SongChart pipeline; no Stage-22.3-specific verifier was introduced. Auto Closure run 377 succeeded on the exact accepted source head and preserved the exact tracked tree.

## Follow-on

Stage 22.4 — Vibe Coding Operations consumes this accepted control plane to accelerate coding, verification and handoff while preserving the same authority and human-gating boundaries.

# SongChart Engineering Graph

Status: human-readable graph topology. This document does not replace machine authorities. It connects the existing authority, verification, regression, impact and current-stage graphs so AI/developers can traverse the repository without inventing another source of truth.

## Graph-of-graphs

```text
                              PROJECT_AUTHORITY
                                     |
             +-----------------------+------------------------+
             |                       |                        |
             v                       v                        v
      DOMAIN / RUNTIME          ENGINEERING              CURRENT STAGE
        AUTHORITIES             AUTHORITIES                STATE
             |                       |                        |
             |                       +--> authority-dependencies.json
             |                       +--> verification-consumer-graph.json
             |                       +--> verification-topology.json
             |                       +--> impact-test-map.json
             |                       +--> regression-ledger.json
             |                                                |
             +----------------------+-------------------------+
                                    |
                                    v
                            IMPACT RESOLUTION
                                    |
                 +------------------+------------------+
                 |                  |                  |
                 v                  v                  v
             AUTHORITY         REVERSE CONSUMER    FOCUSED CHECK
                 |                  |                  |
                 +------------------+------------------+
                                    |
                                    v
                               IMPLEMENT
                                    |
                                    v
                              POST-DIFF IMPACT
                                    |
                                    v
                               RECONCILE
                                    |
                                    v
                                  AUDIT
                                    |
                                    v
                           CANDIDATE / CANONICAL
                                    |
                                    v
                              SEALED EXACT HEAD
```

## Learning graph

Learning is intentionally split into two levels:

```text
RAW FAILURE / USE CASE
          |
          v
AI_LEARNING_LEDGER.md
(provisional evidence / classification)
          |
          | only after correction + permanent machine guard
          v
regression-ledger.json
(durable guarded regression)
          |
          +--> permanent_guards
          |
          +--> verification-consumer-graph.json
          |
          +--> owning machine authority
```

`AI_LEARNING_LEDGER.md` must never become a second regression authority. A durable learning must be promoted to the existing machine authority and, when it represents a reusable regression class, to `regression-ledger.json`.

## Canonical node classes

| Node class | Owner | Meaning |
|---|---|---|
| authority | machine/domain/runtime contract | source of project-specific invariant |
| consumer | verifier / Architecture test / runtime adapter | implements or checks an authority |
| impact rule | `impact-test-map.json` + repository resolver | routes changed paths to owners/checks |
| regression | `regression-ledger.json` | historical failure with permanent guard |
| provisional learning | `AI_LEARNING_LEDGER.md` | evidence not yet promoted to durable law |
| generated | `docs/project/generated/**` | derived exact-tree repository authority |
| current state | `DEVELOPMENT_STATE.md` | operational checkpoint, not product authority |
| closure evidence | candidate/canonical runtime evidence | proof tied to one exact HEAD |

## Canonical edge types

The repository already represents most of these edges across existing contracts. Use these names when reasoning about impact and failure propagation:

```text
authority --owns--> invariant
authority --consumed-by--> verifier/test/runtime surface
path --impacts--> authority
consumer --verifies--> authority
regression --guarded-by--> verifier/test
learning --promotes-to--> regression/authority
generated --generated-from--> authoritative source
command --mutates-within--> mutation envelope
tracked-change --invalidates--> closure evidence
closure evidence --seals--> exact HEAD
stage slice --depends-on--> accepted prior slice
```

## Structural risks and controls

### R1 — Graph fragmentation

Risk: authority dependencies, verification consumers, impact routing and regression history can each be correct locally while an AI fails to traverse between them.

Control: treat this document as the traversal map; machine truth remains in the referenced graphs. Future automation should query the existing machine contracts rather than duplicate their node/edge data here.

### R2 — Learning/regression duplication

Risk: `AI_LEARNING_LEDGER.md` and `regression-ledger.json` both record failures and gradually diverge.

Control: provisional findings live only in the Markdown ledger. Guarded reusable failures are promoted into `regression-ledger.json`; the Markdown entry then points to the promoted regression/authority instead of restating the rule.

### R3 — Current-state Markdown drift

Risk: stage/slice status is human-maintained Markdown and can be parsed ambiguously or become stale.

Control now: parse only the explicit `## Current stage` owner section and verify candidate/repository state. Preferred future improvement: derive a machine-readable development-state node and render Markdown from it, but do not introduce that migration mid-slice without a dedicated task contract.

### R4 — Missing semantic edge types

Risk: a dependency list alone cannot distinguish `generated-from`, `guarded-by`, `invalidates`, and normal `depends-on` relationships.

Control now: use the canonical edge vocabulary above in task/state/learning reasoning. Preferred future improvement: extend the repository resolver/graph schema with typed edges after two or more stages provide concrete query use cases.

### R5 — Cyclic authority

Risk: docs/verifiers can accidentally require each other, creating a cycle where a consumer becomes an authority for its owner.

Control: authority files may own consumers; consumers must not redefine authority-sensitive literals. Generated outputs are leaves derived from source and must never become upstream authority for their own inputs.

### R6 — Verification fan-out explosion

Risk: adding one authority causes too many reverse consumers and makes focused iteration approach full canonical cost.

Control: keep semantic owner rules coarse enough to avoid duplicate verifiers, use focused impact routing during iteration, and preserve full candidate/canonical gates only at closure.

## Stage 18.4 product graph

```text
OPERATOR
   |
   v
ADMIN ATTENTION CENTER
   |
   +--> Provider health ------> Provider operations ------> credential/rate state
   |
   +--> Import failures ------> Import recovery ----------> queue/provider actions
   |
   +--> Quarantine -----------> Canonical admission -----> canonical catalog
   |
   +--> Identity conflicts ---> Review decision ---------> identity mapping
   |
   +--> Users / roles --------> Gate + business invariant -> privileged audit
   |
   `--> System settings ------> governed runtime/provider configuration
```

The Stage 18.4 implementation rule is to close missing edges in this graph, not create parallel Admin subsystems.

## Traversal order for AI/developers

```text
1. DEVELOPMENT_STATE.md        -> where are we?
2. current task contract       -> what is allowed?
3. official/native source      -> what behavior already exists upstream?
4. impact resolver             -> who owns the change?
5. authority-dependencies      -> what depends on the owner?
6. verification-consumer graph -> what must remain synchronized?
7. regression/learning graph   -> has this failure class occurred before?
8. implementation + post-diff  -> what actually changed?
9. candidate/canonical         -> does exact-tree closure hold?
```

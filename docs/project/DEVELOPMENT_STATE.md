# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.3 — Public Metadata & SEO Readiness` merged to `main` via PR #11 after exact-head canonical closure passed.
- Stage `18.3.1 — Verification & AI Workflow Convergence` merged to `main` via PR #12 from exact canonical-verified head `3333e91736a2f02df6959ce98a2ffef7aad07a38`; merge commit `155c9daa14186ae48843ed227eb1af9584e95e03`.
- The accepted development workflow now owns planned/actual impact, reverse verification consumers, generated-authority reconcile, collect-all audit, runtime artifact ownership, one-writer handoff, non-interactive diagnostics and exact-HEAD closure sealing.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub Codespaces is the preferred remote adapter.

## Current stage

- Stage `18.4 — Admin Completion & Operational Convergence`
- Candidate: `v1`
- Branch: `stage-18.4-admin-completion-operational-convergence`
- Candidate closure: pending.

## Stage map

```text
18.4 ADMIN COMPLETION
        |
        +--> [DONE] bootstrap / task contract / candidate authority
        |
        +--> [NOW]  operational attention center
        |              |
        |              +--> failed imports
        |              +--> provider sync failures
        |              +--> pending/running operations
        |              +--> quarantine
        |              +--> identity conflicts
        |              +--> provider health
        |
        +--> [NEXT] provider + credential operational UX
        |
        +--> [NEXT] import progress / retry / failure recovery
        |
        +--> [NEXT] canonical admission + identity conflict completion
        |
        +--> [NEXT] catalog administration gap closure
        |
        +--> [NEXT] users / roles mutation use cases
        |
        +--> [NEXT] privileged audit discoverability
        |
        `--> [FINAL] audit -> candidate -> canonical -> exact-head delivery
```

For repository-wide traversal, read `docs/project/engineering/ENGINEERING_GRAPH.md`; it connects current stage, authority dependencies, impact routing, verification consumers, regression history and closure without replacing their machine owners.

## Done

- Stage 18.4 task contract and validation report initialized from accepted `main`.
- Candidate authority advanced to Stage 18.4 with all closure gates reset to `not_run`.
- Existing Admin inventory confirms delivered foundations already include Dashboard, provider/import operations, canonical admission, identity conflicts, catalog, users, system settings and privileged audit routes/views.
- First Stage 18.4 product slice exposes previously hidden dashboard attention signals for recent provider-sync failures and pending/running extension operations.
- `docs/project/engineering/AI_LEARNING_LEDGER.md` records only provisional learning/history; durable reusable failures promote to `regression-ledger.json`, their owning authority and permanent verification guard.
- `docs/project/engineering/ENGINEERING_GRAPH.md` now provides a graph-of-graphs traversal layer over existing machine authorities instead of introducing another truth source.

## In progress

### Slice 18.4-A — Operational Attention Center

Goal: an operator should see actionable problems from the Admin landing page before opening technical detail.

Current source surface:

- `app/Support/Admin/AdminDashboardSnapshot.php`
- `resources/views/admin/dashboard.blade.php`
- `tests/Feature/AdminOperationsUxTest.php`

Expected focused verification:

```bash
./songchart impact --diff
./songchart dev test --no-build tests/Feature/AdminOperationsUxTest.php
./songchart composer exec pint -- --test
./songchart composer exec phpstan analyse
```

## Next

1. Provider + credential operational UX: classify what is runtime-configurable in Admin vs environment/deployment-owned; never render secret values.
2. Import operations: make progress, retryable/terminal state and bounded recovery legible without engineering tools.
3. Canonical admission + identity conflicts: verify end-to-end operator decisions, authorization and privileged audit evidence.
4. Catalog administration: inventory current entity support and add only accepted missing write workflows.
5. Users/roles: current surface is read-only; define explicit mutation use cases before adding writes, preserving Gate and last-super-admin invariants.
6. Privileged audit: ensure every supported Admin mutation is discoverable from the audit viewer.

## Current blockers / risks

- Graph fragmentation: authority dependencies, verification consumers, impact routing and regression history are separate machine graphs; use `ENGINEERING_GRAPH.md` for traversal and do not duplicate their data into another machine authority.
- Learning/regression duplication: provisional AI learnings must promote to `regression-ledger.json` + owner + guard instead of becoming a second rule set.
- Current-state Markdown drift remains possible; current mitigations are explicit-section parsing and repository-state/candidate verification. A machine-readable development-state migration is deferred to a dedicated task rather than introduced mid-slice.
- Semantic edge typing is incomplete: existing graphs mainly express ownership/dependents, while `generated-from`, `guarded-by`, `invalidates` and `promotes-to` are reasoning conventions rather than one unified schema. Do not expand schemas without concrete cross-stage query use cases.
- Cyclic authority is forbidden: consumers/verifiers may check authority but must never become upstream truth for their own authority inputs; generated outputs remain derived leaves.
- Verification fan-out can grow too broad; keep one semantic owner per invariant and use focused impact during iteration rather than adding duplicate verifiers.
- Provider credential UX must never expose secrets and must remain within existing credential-pool/provider authorities.
- Canonical admission, identity conflicts, catalog and user-role mutations must preserve Laravel Gate authorization, application write boundaries and privileged audit evidence.
- Dashboard/operational read models can create query amplification; use existing registered read-model/query-budget authorities instead of adding controller queries.
- User/role writes are not accepted merely because a read-only page exists; mutation semantics must be defined first.
- System Settings must not turn deployment-owned `.env`/secret configuration into unsafe database-editable state.
- No new provider breadth, custom admin framework, OAuth program or advanced scheduler should enter scope without a task-contract deviation backed by a real use case.

## AI/dev learning discipline

Use `docs/project/engineering/ENGINEERING_GRAPH.md` for traversal and `docs/project/engineering/AI_LEARNING_LEDGER.md` only for provisional findings.

```text
USE CASE / FAILURE
      |
      v
EXACT EVIDENCE
      |
      v
ROOT CAUSE + OWNER
      |
      v
CORRECTION
      |
      v
FOCUSED TEST / VERIFIER
      |
      v
PERMANENT GUARD?
   /             \
 no               yes
 |                 |
provisional     promote to owner
learning            |
                    +--> regression-ledger.json when reusable
                    +--> verification consumer graph
                    `--> permanent guard
```

Do not teach future AI from a one-off workaround, raw log or unverified assumption. Official framework/provider behavior should be checked against current official sources; SongChart-specific semantics remain owned by repository authorities.

## Latest focused evidence

- Stage 18.3.1 exact-head candidate/canonical/close passed on `3333e91736a2f02df6959ce98a2ffef7aad07a38` before PR #12 merge.
- PR #12 merged into `main` at `155c9daa14186ae48843ed227eb1af9584e95e03`.
- Stage 18.4 branch was created directly from that accepted merge commit.
- Stage 18.4-A source + focused regression have been committed remotely; local Docker verification is still pending.
- Graph topology review identified graph fragmentation, learning/regression duplication, current-state Markdown drift, missing typed semantic edges, cyclic-authority risk and verification fan-out as explicit engineering risks; controls are documented without adding duplicate machine truth.

## Next required action

1. Sync local branch with remote Stage 18.4 head.
2. Run preflight authority consistency and `./songchart context --json`.
3. Run `./songchart impact --diff` and the focused Admin UX test.
4. Run Pint/PHPStan for the touched PHP/test surface.
5. Run documentation/repository-state verification because graph traversal docs changed.
6. If focused evidence is green, continue to provider/credential inventory rather than broadening the dashboard slice.

## Documentation checkpoint discipline

For every logical implementation commit:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` as durable project overview/bootstrap, not current-stage state storage;
- keep `docs/project/DEVELOPMENT_STATE.md` as the operational current-state owner;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- keep completed-stage chronology in `docs/project/DEVELOPMENT_HISTORY.md` after governed acceptance;
- use the AI learning ledger only as provisional evidence/history; promote durable rules into their owning authority and regression graph;
- never duplicate workflow authority into `AGENTS.md`, `CLAUDE.md`, `GEMINI.md` or nested compatibility copies.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift. Prefer the secret-redacted `./songchart ai doctor` bundle over manually copying raw environment/log output.

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

## Done

- Stage 18.4 task contract and validation report initialized from accepted `main`.
- Candidate authority advanced to Stage 18.4 with all closure gates reset to `not_run`.
- Existing Admin inventory confirms delivered foundations already include Dashboard, provider/import operations, canonical admission, identity conflicts, catalog, users, system settings and privileged audit routes/views.
- First Stage 18.4 product slice exposes previously hidden dashboard attention signals for recent provider-sync failures and pending/running extension operations.
- `docs/project/engineering/AI_LEARNING_LEDGER.md` now records reusable use-case/debug/failure learnings without becoming a parallel authority; durable rules require an owning authority plus permanent machine guard.

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

- Provider credential UX must never expose secrets and must remain within existing credential-pool/provider authorities.
- Canonical admission, identity conflicts, catalog and user-role mutations must preserve Laravel Gate authorization, application write boundaries and privileged audit evidence.
- Dashboard/operational read models can create query amplification; use existing registered read-model/query-budget authorities instead of adding controller queries.
- User/role writes are not accepted merely because a read-only page exists; mutation semantics must be defined first.
- System Settings must not turn deployment-owned `.env`/secret configuration into unsafe database-editable state.
- No new provider breadth, custom admin framework, OAuth program or advanced scheduler should enter scope without a task-contract deviation backed by a real use case.

## AI/dev learning discipline

Use `docs/project/engineering/AI_LEARNING_LEDGER.md` to recognize known failure classes early.

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
provisional     promote rule
learning        to real authority
```

Do not teach future AI from a one-off workaround, raw log or unverified assumption. Official framework/provider behavior should be checked against current official sources; SongChart-specific semantics remain owned by repository authorities.

## Latest focused evidence

- Stage 18.3.1 exact-head candidate/canonical/close passed on `3333e91736a2f02df6959ce98a2ffef7aad07a38` before PR #12 merge.
- PR #12 merged into `main` at `155c9daa14186ae48843ed227eb1af9584e95e03`.
- Stage 18.4 branch was created directly from that accepted merge commit.
- Stage 18.4-A source + focused regression have been committed remotely; local Docker verification is still pending.

## Next required action

1. Sync local branch with remote Stage 18.4 head.
2. Run preflight authority consistency and `./songchart context --json`.
3. Run `./songchart impact --diff` and the focused Admin UX test.
4. Run Pint/PHPStan for the touched PHP/test surface.
5. If focused evidence is green, continue to provider/credential inventory rather than broadening the dashboard slice.

## Documentation checkpoint discipline

For every logical implementation commit:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` as durable project overview/bootstrap, not current-stage state storage;
- keep `docs/project/DEVELOPMENT_STATE.md` as the operational current-state owner;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- keep completed-stage chronology in `docs/project/DEVELOPMENT_HISTORY.md` after governed acceptance;
- use the AI learning ledger only as evidence/history; promote durable rules into their owning authority;
- never duplicate workflow authority into `AGENTS.md`, `CLAUDE.md`, `GEMINI.md` or nested compatibility copies.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift. Prefer the secret-redacted `./songchart ai doctor` bundle over manually copying raw environment/log output.

# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.3 — Public Metadata & SEO Readiness` merged to `main` via PR #11 after exact-head canonical closure passed.
- Stage `18.3.1 — Verification & AI Workflow Convergence` merged to `main` via PR #12 from exact canonical-verified head `3333e91736a2f02df6959ce98a2ffef7aad07a38`; merge commit `155c9daa14186ae48843ed227eb1af9584e95e03`.
- The accepted development workflow owns planned/actual impact, reverse verification consumers, generated-authority reconcile, collect-all audit, runtime artifact ownership, one-writer handoff, non-interactive diagnostics and exact-HEAD closure sealing.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub Codespaces is the preferred remote adapter.

## Current stage

- Stage `18.4 — Admin Completion & Operational Convergence`
- Candidate: `v1`
- Branch: `stage-18.4-admin-completion-operational-convergence`
- Candidate closure: pending.
- Strategy: **close-first**. Complete only accepted operational gaps that block Stage 18.4 acceptance; do not introduce a new graph engine, AI Ops runtime, workflow framework or broad layer refactor before closure.

## Stage map

```text
18.4 ADMIN COMPLETION
        |
        +--> [DONE] bootstrap / task contract / candidate authority
        |
        +--> [DONE*] operational attention-center source slice
        |              `--> local focused verification pending
        |
        +--> [CHECK] provider + credential operational UX
        |
        +--> [CHECK] import progress / retry / failure recovery
        |
        +--> [CHECK] canonical admission + identity conflict completion
        |
        +--> [CHECK] catalog administration gaps
        |
        +--> [CHECK] accepted users / roles mutation use cases
        |
        +--> [CHECK] privileged audit discoverability
        |
        `--> [FINAL] reconcile -> audit -> focused gates -> candidate -> canonical -> exact-head delivery
```

`[CHECK]` means inventory the existing surface against the task-contract acceptance criterion. If the capability is already complete enough, record evidence and mark it done instead of creating more code.

## Done

- Stage 18.4 task contract and validation report initialized from accepted `main`.
- Candidate authority advanced to Stage 18.4 with closure gates reset for the stage.
- Existing Admin inventory confirms delivered foundations already include Dashboard, provider/import operations, canonical admission, identity conflicts, catalog, users, system settings and privileged audit routes/views.
- First Stage 18.4 product slice exposes previously hidden dashboard attention signals for recent provider-sync failures and pending/running extension operations.
- `docs/project/engineering/AI_LEARNING_LEDGER.md` is development-only evidence/navigation memory. Durable rules still belong to existing repository authorities plus permanent guards.
- `docs/project/engineering/ENGINEERING_GRAPH.md` provides graph-of-graphs navigation without creating another machine source of truth.
- `README.md` is now the developer onboarding/FAQ entrypoint: first-ten-minutes commands, source placement map, collaboration rules, failure handling and authority routing.
- Roadmap now separates product stages from cross-stage engineering optimization and repository simplification.

## In progress

### Stage 18.4 closure inventory

Goal: prove whether the remaining task-contract areas are already operationally sufficient before adding code.

For each remaining area use:

```text
ACCEPTANCE CRITERION
        |
        v
EXISTING ROUTE / VIEW / READ MODEL / ACTION
        |
        +--> complete ------> record focused evidence, DONE
        |
        +--> partial -------> smallest missing edge only
        |
        `--> missing -------> accepted minimal use case before implementation
```

Do not perform broad `Application` / `Actions` / `Services` / `Support` restructuring in this stage. New code should choose the clearest existing owner; structural convergence is opportunistic only when the touched use case makes the ownership problem concrete.

## Codespaces close-first command lane

After syncing remote head:

```bash
./songchart ai doctor
./songchart context --json

./songchart composer candidate-contract:verify
./songchart composer repository-state:verify
./songchart composer runtime-artifact:verify
./songchart composer impact-map:verify

./songchart impact --diff
./songchart dev test --no-build tests/Feature/AdminOperationsUxTest.php
./songchart composer exec pint -- --test
./songchart composer exec phpstan analyse
```

Then inventory each `[CHECK]` area. Add code only for a concrete missing acceptance edge. When no blocker remains:

```bash
./songchart reconcile
git status --short
./songchart audit

./songchart candidate
./songchart close

git rev-parse HEAD
git status --short
```

Any tracked change after canonical PASS invalidates the previous closure evidence.

## Current blockers / risks

- Provider credential UX must never expose secrets and must remain within existing credential-pool/provider authorities.
- Canonical admission, identity conflicts, catalog and user-role mutations must preserve Laravel Gate authorization, application write boundaries and privileged audit evidence.
- Dashboard/operational read models can create query amplification; use registered read-model/query-budget authorities instead of adding controller queries.
- User/role writes are not accepted merely because a read-only page exists; only explicit accepted mutation use cases belong in 18.4.
- System Settings must not turn deployment-owned `.env`/secret configuration into unsafe database-editable state.
- Application/Actions/Services/Support overlap is an architectural ambiguity risk; do not solve it with a mass move during closure.
- Verification/authority graph growth must not become a second application. New standards/contracts require a concrete failure class or navigation/verification benefit.
- AI development memory must not become product/runtime authority or an AI Ops permission system.

## Cross-stage engineering direction after 18.4

These are non-blocking engineering improvements, not new product requirements:

1. derive better graph queries from existing authorities/consumer/impact/regression graphs rather than create a duplicate knowledge graph;
2. use `.agents/skills/*` as thin AI routers to bounded contexts and authorities;
3. consider machine-readable current-stage state only if Markdown checkpoint drift recurs;
4. enforce mutation envelopes only after enough real command evidence exists;
5. cluster audit failures by root cause when it measurably reduces debug iterations;
6. prototype AI Operations later as read-only `observe → explain → recommend`, with governed human-approved actions only through Laravel Gate/Application Action/Audit boundaries.

## Repository simplification direction

Retire only after proving no active consumer:

- historical root/stage change manifests from active navigation;
- duplicated workflow prose in `AGENTS.md`, `CLAUDE.md`, `GEMINI.md` beyond thin routing;
- stale `.agents/skills/*` content that copies rules instead of routing to owners;
- dead verification aliases/compatibility scripts/references;
- duplicate prose docs whose active content has a single current owner;
- tracked runtime/generated artifacts that do not own durable source truth.

Do not mass-delete historical evidence or move broad source trees during 18.4 closure.

## AI/dev learning discipline

Use `docs/project/engineering/AI_LEARNING_LEDGER.md` only to recognize known development failure/navigation patterns early.

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
learning        to real authority/regression guard
```

Do not teach future AI from a one-off workaround, raw log or unverified assumption. Official framework/provider behavior should be checked against current official sources; SongChart-specific semantics remain owned by repository authorities.

## Latest focused evidence

- Stage 18.3.1 exact-head candidate/canonical/close passed on `3333e91736a2f02df6959ce98a2ffef7aad07a38` before PR #12 merge.
- PR #12 merged into `main` at `155c9daa14186ae48843ed227eb1af9584e95e03`.
- Stage 18.4 branch was created directly from that accepted merge commit.
- Stage 18.4-A source + focused regression are committed remotely; local Docker verification is still pending.
- Repository-wide topology review identified layer ambiguity and graph fragmentation as cross-stage engineering concerns, not reasons to delay 18.4 product closure.

## Next required action

1. Sync Codespaces to the remote Stage 18.4 head.
2. Run the close-first command lane above.
3. Inventory each `[CHECK]` task-contract area against existing implementation and record evidence before writing more code.
4. Implement only concrete missing operational edges.
5. When acceptance is satisfied, reconcile → audit → focused gates → candidate → canonical immediately; do not continue architecture optimization inside the closed stage.

## Documentation checkpoint discipline

For every logical implementation commit:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` as durable onboarding/overview, not current-stage state storage;
- keep `docs/project/DEVELOPMENT_STATE.md` as the operational current-state owner;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- keep completed-stage chronology in `docs/project/DEVELOPMENT_HISTORY.md` after governed acceptance;
- use the AI learning ledger only as development evidence/history; promote durable rules into their owning authority;
- never duplicate workflow authority into `AGENTS.md`, `CLAUDE.md`, `GEMINI.md` or nested compatibility copies.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift. Prefer the secret-redacted `./songchart ai doctor` bundle over manually copying raw environment/log output.

# Stage 29.0 — Delivery Velocity & Roadmap Automation

## Status

Active on branch `stage-29-delivery-velocity-roadmap-automation` from accepted `main@b29483c4e15cac95129ae99806c0fcc670fb2ffe` after Stage 28 post-merge verification passed in workflow run `35190428504`. `docs/project/engineering/stage-plan.json` remains the authored execution authority.

## Goal

Reduce delivery latency without weakening SongChart verification or roadmap authority. Normal implementation pushes must use focused impact-selected verification; full PostgreSQL/browser/canonical exact-head closure must run only at an explicit promotion boundary. Roadmap progress must be compiled from authored intent plus Git/GitHub runtime evidence rather than repeatedly committing volatile CI facts.

Target flow:

```text
IMPLEMENT
  -> focused/impact verification
  -> fast PR CI
  -> continue implementation
  -> Ready for review promotion gate
  -> full exact-head closure
  -> explicit authored acceptance
  -> human merge
```

## Invariants

1. Existing SongChart verifiers remain authoritative; Stage 29 changes orchestration, not verification semantics.
2. `./songchart impact --verify` remains the focused execution owner.
3. `./songchart verify` remains the canonical closure owner.
4. PostgreSQL 18, browser smoke and production frontend build remain mandatory for promotion closure unless a future authority explicitly changes their gate profile.
5. Draft PRs are implementation work leases and must not trigger full promotion closure on every source commit.
6. Moving a PR from Draft to Ready for review is the explicit G4 promotion boundary for the normal GitHub workflow.
7. Any source commit after a PR is Ready invalidates prior closure evidence and must rerun full closure.
8. Generated authority remains generator-owned; ephemeral fast checks may prepare it locally but must not push it.
9. Strategic roadmap candidates do not become active stages automatically.
10. Runtime facts such as current PR head, CI run and derived verification state are projections, not authored roadmap history.
11. Stage/tranche acceptance and production promotion remain explicit human/authorized-operator transitions.
12. No new PM platform, queue, workflow engine, analytics service or second verification framework is introduced.

## Tranches

### 29.0A — Fast Inner Loop

Goals:

- add a lightweight PR workflow that runs the existing impact-selected verification owner;
- prepare generated authority ephemerally for focused verification without pushing a generated commit;
- cancel obsolete fast runs when the PR head advances;
- keep Draft PR source commits free of full PostgreSQL/browser/canonical closure.

### 29.0B — Explicit Promotion Closure

Goals:

- change Auto Closure from every Draft source synchronize to the promotion boundary;
- run full closure when a PR becomes Ready for review;
- rerun full closure for any later source commit while the PR remains non-draft;
- preserve effective exact SHA, generated-authority preparation, full runtime lanes, canonical CLOSE and ready-to-promote semantics.

### 29.0C — Compiled Roadmap Runtime State

Goals:

- add a machine-readable strategic candidate registry separate from active execution authority;
- compile authored stage/tranche state plus exact runtime verification evidence into a read-only derived state;
- surface roadmap/runtime state through the existing Delivery Kernel `status` facade rather than adding another public command;
- keep runtime state out of authored `ROADMAP.md` and stage history.

### 29.0D — Workflow & Authority Closure

Goals:

- lock the new orchestration and roadmap-state boundaries with executable regression tests;
- verify Draft PR fast-only behavior and Ready-for-review full-closure behavior;
- prove no existing canonical verifier was weakened or duplicated;
- close Stage 29 on one full exact-head promotion run and keep merge human-controlled.

## Explicit non-goals

- Product feature expansion.
- Provider integration work.
- Redesigning `tests.yml` into a new test framework.
- Replacing GitHub Actions with another CI/CD platform.
- Persisting live PR/workflow state into authored roadmap files.
- Automatically activating roadmap candidates from telemetry.
- Automatically merging or deploying production.
- Backstage, Temporal, Kafka, workflow SaaS or another project-management source of truth.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/DELIVERY_KERNEL.md`
- `docs/project/engineering/delivery-kernel.json`
- `docs/project/engineering/stage-plan.json`
- `docs/project/docs/ROADMAP.md`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/stack/impact-test-map.json`
- `scripts/resolve-repository-impact.php`
- `scripts/run-impact-verification.sh`
- `scripts/delivery-kernel.sh`
- `.github/workflows/tests.yml`
- `.github/workflows/auto-closure.yml`

### Installed versions

Repository lockfiles and workflow/container definitions remain executable version authority. Stage 29 adds no application package dependency and keeps the accepted PHP 8.5, Laravel 13, PostgreSQL 18, Redis, Node and Playwright baselines.

### Official external sources

GitHub Actions event semantics and pull-request Draft/Ready state are the only external platform behavior materially used by this stage. Where platform semantics need verification, GitHub's current official Actions and pull-request documentation is authoritative. No third-party CI guidance or generic blog workflow becomes SongChart authority.

### Native capability assessment

SongChart already has impact resolution, focused verification, candidate/canonical closure, exact-head classification, concurrency cancellation, reusable full verification lanes and Delivery Kernel lifecycle states. Stage 29 must compose these capabilities rather than create new verifiers. GitHub already exposes Draft/Ready transitions and PR synchronize events, so they are preferred over introducing a new promotion label or command.

### Custom implementation justification

Custom code is limited to orchestration and projection gaps that existing owners do not currently express: a fast PR adapter, promotion-gated Auto Closure trigger rules, a small roadmap candidate registry, and a read-only roadmap-state compiler. These components delegate to existing verification owners and cannot grant acceptance, merge, deployment or canonical mutation authority.

## Tests and verification

Focused development verification:

```bash
./songchart status
./songchart plan
./songchart check
```

Promotion closure reuses the accepted full path:

```bash
./songchart impact --verify
./songchart candidate
./songchart verify
```

Regression coverage must prove:

- the fast workflow delegates to `./songchart impact --verify`;
- fast PREPARE does not push generated authority;
- full Auto Closure does not execute for Draft synchronize events;
- Ready-for-review and subsequent non-draft source commits execute the full exact-head path;
- `roadmap-state.php` never treats clean Git state alone as verified;
- runtime verification only upgrades derived state when evidence matches the exact current head;
- roadmap candidates remain non-activating metadata;
- Delivery Kernel status consumes the compiler without adding a competing lifecycle.

## Handoff rule

Stage 29 uses one bounded branch/PR. The PR should be opened as Draft only after the initial implementation batch is assembled. Draft pushes run fast CI only. When focused verification is green and the implementation batch is complete, transition the PR to Ready for review once to request full closure. Any fixes after that are allowed, but they invalidate exact-head evidence and automatically rerun full closure. Acceptance metadata is written only after a successful exact-head promotion run.
# SongChart Delivery Kernel

Status: active workflow-convergence authority subordinate to `PROJECT_AUTHORITY.md` and `DELIVERY_WORKFLOW.md`.

## Purpose

The Delivery Kernel reduces workflow and governance growth without weakening existing verification. It is a strangler layer over current impact, candidate, canonical, Git and GitHub authorities. Existing verifiers remain authoritative until an equivalent generic/declarative consumer is proven and the owning authority explicitly retires the bespoke mechanism.

## Five primitives

Every delivery change is modeled through five primitives:

1. **Owner** — the semantic/domain/runtime authority that owns the meaning being changed.
2. **Change** — the bounded promotion unit being implemented.
3. **Risk** — one or more stable risk classes from `delivery-kernel.json`.
4. **Evidence** — the gate profile and impacted behavioral/static evidence required for the exact tree.
5. **Promotion** — the allowed lifecycle transition toward accepted `main` and release.

## Risk classes

- `R1 identity`: semantic ownership, canonical identity and duplicate meaning drift.
- `R2 boundary`: layer, authorization, mutation and persistence boundary violations.
- `R3 state`: source/generated/schema/context drift.
- `R4 runtime`: environment, queue, scheduler and production lifecycle risk.
- `R5 delivery`: Git provenance, branch divergence, exact-SHA evidence and promotion risk.
- `R6 recovery_security`: recovery failure, secret exposure, external-input and security risk.

These classes replace ad-hoc risk vocabulary in new workflow mechanisms. Domain-specific acceptance language may remain, but it maps back to these stable classes instead of creating another workflow taxonomy.

## Gate profiles

- `G0 advisory`: documentation/advisory ownership checks.
- `G1 code_local`: formatting/static/Unit/Architecture/local contracts.
- `G2 application_domain`: G1 plus impacted Feature/domain/authorization/audit behavior.
- `G3 persistence_runtime`: G2 plus PostgreSQL/runtime/Redis/migration evidence selected by impact.
- `G4 promotion_release`: impact verification, candidate, canonical, exact-SHA and CI promotion evidence.

Profiles do not replace impact resolution. They bound the expected verification class while `./songchart impact` remains the source of exact affected consumers.

## Lifecycle

Only these delivery states are introduced by the kernel:

```text
PLANNED -> IMPLEMENTING -> CHECKED -> CLOSED -> CI_VERIFIED -> ACCEPTED -> RELEASED
```

- `CHECKED` means impacted/focused evidence is green for the current tree.
- `CLOSED` means canonical closure is green on an exact clean committed tree.
- `CI_VERIFIED` means GitHub CI is green on that same SHA.
- `ACCEPTED` means the exact change is merged to `main`.
- `RELEASED` means the release/tag/artifact comes from an accepted `main` SHA.

Human-friendly stage labels may describe roadmap grouping but must not create competing lifecycle meanings.

## Public facade target

The target mental model is intentionally small:

```text
songchart status   where am I?
songchart plan     what does this change affect?
songchart check    run required focused verification
songchart close    canonical exact-tree closure
songchart promote  exact-tree promotion preflight
songchart doctor   explain environment/workflow failures
```

The first kernel implementation lives in `scripts/delivery-kernel.sh` and delegates to existing SongChart owners. Migration into the root facade is incremental and must preserve existing public command compatibility until command-surface regression proves the replacement.

## Tap-only GitHub mobile control plane

Constrained mobile operators must not be required to copy or type Git commands, branch names, SHAs or verification commands as part of normal delivery. `.github/workflows/songchart-mobile.yml` is the tap-only GitHub Actions adapter and offers exactly two actions:

```text
CHECK  -> ./mobile check --verbose
CLOSE  -> ./songchart verify
```

`CHECK` keeps the compact mobile adapter over impact-selected verification. Remote `CLOSE` intentionally uses the read-only canonical entrypoint instead of `./mobile close`/`./songchart close`: local close may prepare and commit generated authority, while GitHub Actions must verify the selected exact SHA without manufacturing another commit. The workflow therefore records the selected SHA before execution and fails if HEAD moves or tracked source is mutated.

The GitHub mobile workflow is a control-plane adapter only. It may not push source, commit generated authority, skip gates, cache a prior PASS as current evidence or reimplement verification semantics in workflow YAML. The workflow becomes available for normal mobile operation only after its definition is present on the accepted default branch.

## Anti-growth rules

1. **No new bespoke verifier by default.** A new `verify-*.php` must demonstrate that the invariant cannot be represented by an existing/generic declarative contract or by an existing behavioral test owner.
2. **No new public command by default.** Prefer routing through the kernel facade and existing canonical entrypoints.
3. **No new authority by default.** Resolve the current semantic owner before creating another document or machine contract.
4. **Branch ownership follows a bounded promotion unit.** Roadmap stages may contain multiple independently accepted promotion units; do not require a mega-branch merely because work shares a stage number.
5. **A permanent workflow rule must reduce ambiguity.** A new rule that only duplicates old prose without replacing or compiling it is not accepted convergence.

## Declarative versus behavioral invariants

Static/source-structure invariants should migrate toward generic declarative registration. Behavioral business/runtime invariants remain executable tests. Do not convert meaningful behavioral assertions into string-scanning metadata merely to reduce file count.

## Strangler migration

The migration sequence is:

```text
existing authority/verifier
        ↓
register equivalent kernel owner/risk/profile
        ↓
run both paths and prove equivalent behavior
        ↓
route public facade through kernel
        ↓
retire only genuinely redundant mechanism
```

No existing candidate, canonical, PostgreSQL, migration, security or provider gate is weakened by introducing this kernel.

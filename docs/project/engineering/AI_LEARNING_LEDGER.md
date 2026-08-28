# AI Learning Ledger

Status: evidence ledger only. This file is not a parallel source of truth. Durable rules must be promoted into their owning repository authority and protected by an executable guard/test before they are treated as workflow law.

## Purpose

Capture recurring use cases, debug findings and failed-verification patterns so AI/developers can recognize known failure classes early, reduce repetitive diagnosis and avoid speculative fixes.

The ledger records evidence and learning history. It does not override `PROJECT_AUTHORITY.md`, task contracts, machine contracts, domain authorities or verification topology.

## Admission rule

A learning may be added only when all of the following are known:

1. observable use case or exact failure evidence;
2. root-cause classification;
3. owning authority/surface;
4. corrective action actually taken;
5. focused verification proving the correction;
6. permanent guard/test/verifier when the learning is meant to become durable.

If item 6 does not exist, record the finding as provisional and do not teach future AI to treat it as a mandatory rule.

## Learning lifecycle

```text
USE CASE / FAILURE
       |
       v
PRESERVE EXACT EVIDENCE
       |
       v
CLASSIFY ROOT CAUSE
       |
       v
FIND OWNING AUTHORITY
       |
       +---- authority stale ------> FIX AUTHORITY
       |
       +---- consumer stale -------> FIX CONSUMER
       |
       +---- generated stale ------> REGENERATE
       |
       +---- runtime artifact -----> FIX OWNERSHIP
       |
       +---- product behavior -----> FIX SOURCE/USE CASE
       |
       v
FOCUSED REGRESSION
       |
       v
PERMANENT MACHINE GUARD?
       |                 |
      no                yes
       |                 |
       v                 v
PROVISIONAL          PROMOTE RULE
LEARNING             TO OWNER
                         |
                         v
                   CLOSE EXACT HEAD
```

## Failure taxonomy

| Class | Early signal | Preferred response |
|---|---|---|
| formatter drift | Pint-only failure | run locked Pint; do not rewrite behavior |
| stale authority consumer | verifier expects obsolete structure | keep current authority; repair consumer |
| parser ambiguity | wrong stage/state selected from mixed-history Markdown | parse owning section or machine authority |
| generated authority drift | source fingerprint/manifest mismatch | regenerate; never hand-merge generated JSON |
| runtime artifact ownership | tracked mutable runtime evidence | move evidence to ignored runtime surface; keep only exact sentinels allowlisted |
| dead workflow route | missing/retired command or test target | repair executable impact/command authority |
| command UX blocking | pager/prompt in non-interactive workflow | use native non-interactive primitive such as Git `--no-pager` |
| post-closure mutation | HEAD/tree changes after canonical PASS | invalidate evidence and re-close exact HEAD |
| application data-boundary violation | controller opens query/persistence boundary | move reads/writes to registered read/action surface |
| operator visibility gap | state exists in backend but no Admin action path | expose actionable, authorized Admin attention item |

## Accepted learnings

### L-001 — Runtime evidence must not become tracked source

- Evidence: canonical/runtime verification previously surfaced mutable snapshots under tracked `storage/framework/**`.
- Root cause: runtime evidence and source ownership were mixed.
- Owner: runtime artifact ownership / verification workflow.
- Durable guard: `scripts/verify-runtime-artifact-ownership.php`.
- Rule promoted: mutable runtime evidence remains runtime-only; exact `.gitignore` directory sentinels may be allowlisted.

### L-002 — Current state must be parsed from its owning section

- Evidence: candidate verification read accepted Stage 18.3 history instead of current Stage 18.3.1.
- Root cause: first-match Markdown parsing across mixed historical/current content.
- Owner: candidate/repository-state consumers.
- Durable guard: corrected section-scoped parser plus workflow regression coverage.
- Rule promoted: machine-critical state uses machine authority where possible; Markdown consumers parse the explicit owner section.

### L-003 — Generated authority follows source, not manual conflict resolution

- Evidence: project context/repository manifest changed after authority/source changes.
- Root cause: generated fingerprints were stale by design after source evolution.
- Owner: repository compiler / generated authority workflow.
- Durable guard: `./songchart reconcile` + repository compiler verification.
- Rule promoted: regenerate generated JSON from the final tree; do not hand-merge it.

### L-004 — Non-interactive commands must not open a pager

- Evidence: `./songchart reconcile` appeared to hang at `(END)` while `git diff` was inside `less`.
- Root cause: diagnostic command inherited Git pager behavior.
- Owner: SongChart CLI workflow UX.
- Durable guard: workflow regression requires Git-native `--no-pager` for reconcile diff.
- Rule promoted: automation/AI workflow commands must return control without hidden pager interaction.

### L-005 — Closure evidence belongs to one exact HEAD

- Evidence: workflow hardening after canonical PASS produced a new tracked tree.
- Root cause: valid change occurred after closure.
- Owner: exact-tree candidate/canonical delivery authority.
- Durable guard: post-closure seal discipline and PR latest-head checks.
- Rule promoted: any tracked change after PASS invalidates old closure evidence.

## Stage 18.4 provisional learning queue

Use this section for findings that are not yet durable rules.

| ID | Use case / failure | Status | Required before promotion |
|---|---|---|---|
| P-184-001 | Admin operational state exists but may not be actionable from the dashboard | active | complete inventory, focused UX test, confirm owner/action routes |
| P-184-002 | User/role Admin surface is currently read-only | active | accepted mutation use cases + Gate/business invariant/audit design |
| P-184-003 | System Settings mixes runtime diagnostics and configurable provider settings | active | classify supported runtime-configurable vs environment-owned settings |

## Debug discipline

When debugging, do not append raw logs or a diary here. Preserve only the minimal evidence needed to identify a reusable failure class. If a finding is one-off and has no reusable guard, keep it in the current task/validation evidence instead.

# SongChart Delivery & Upgrade Workflow

Status: mandatory delivery authority for implementation stages and corrective candidates.

## Source-of-truth rule

GitHub is the only development handoff and upgrade source of truth.

- One stage uses one stage branch.
- Logical implementation/corrective slices are commits on that branch.
- Device or AI handoff happens through committed/pushed Git state, never source ZIP copying.
- A stage closes through one pull request into `main` after exact-tree candidate and canonical verification.
- Tags/releases are created only from an accepted `main` tree.

Do not create incremental source ZIPs, full Laragon-ready ZIPs, patch installers, or host-specific apply scripts for normal development handoff.

## Golden delivery workflow

```text
accepted main baseline
    ↓
stage branch
    ↓
planned impact + official/native capability review
    ↓
logical implementation commits + focused verification
    ↓
actual-diff impact + generated-authority reconcile
    ↓
collect-all audit + focused gates
    ↓
push to GitHub / Draft PR when useful
    ↓
exact-tree candidate preparation + stage verification
    ↓
canonical verification
    ↓
push exact closed HEAD
    ↓
PR CI green
    ↓
merge to main
    ↓
release/tag when required
```

Git history is the rollback and provenance mechanism. Do not reconstruct target state from copied folders or packaging trees.

## One writer per surface

During AI-assisted development, one writer owns a source surface until the next synchronization point.

- If an AI pushes a corrective commit that touches a file, the local workspace fetches and integrates that commit before making another edit to the same file.
- If the local branch has diverged after a rebase, do not keep writing competing remote commits to the same files. Prefer a bounded corrective commit and local cherry-pick, or finish the local integration first.
- Before changing writer, publish the current intended state as a commit SHA and run/review `./songchart impact --diff` when the handoff could expand the affected surface.
- Never resolve generated JSON conflicts by hand when the artifact can be regenerated from authoritative source.

This rule prevents the same-source replay/conflict class seen when upstream formatting/authority fixes and local historical commits are edited concurrently.

## Rebase and conflict handling

Rebase replays local commits onto a new base. During a rebase, resolve source conflicts according to semantic authority, not by mechanically choosing a side.

- Inspect `git status` and the current patch before resolving.
- `git rebase --continue` is used only after every source conflict is intentionally resolved and staged.
- `git rebase --skip` is appropriate for a replayed commit whose entire effect is obsolete/derived, such as a stale generated-only refresh that will be regenerated from the final tree.
- `git rebase --abort` restores the pre-rebase branch when the integration strategy is no longer trustworthy.
- Generated repository authority is dropped/regenerated from the final exact tree rather than manually merged.
- After any rebase that replayed PHP source, rerun impact-selected Pint/PHPStan/focused tests; do not assume an upstream formatter commit still dominates replayed historical source.

Official Git semantics: https://git-scm.com/docs/git-rebase

## Device and AI handoff

Before switching device or execution agent:

1. inspect `./songchart ai status`;
2. run `./songchart impact --diff` when the stage has implementation changes;
3. commit the intended logical state;
4. push the current stage branch;
5. communicate the exact commit SHA that owns the handoff;
6. on the next environment, fetch and fast-forward/reset/cherry-pick only after reviewing divergence;
7. use `./songchart ai doctor` for runtime/test evidence that is not represented in Git.

Never use cross-device stash as a handoff mechanism.

## Verification during delivery

During iteration use the dependency-aware workflow:

```bash
./songchart impact <planned-paths...>
# implement
./songchart impact --diff
./songchart reconcile
./songchart audit
./songchart dev test <test-path>
```

`./songchart audit` is diagnostic and collect-all. It does not replace fail-fast `quality:verify`, candidate or canonical closure.

Stage candidate closure:

```bash
./songchart candidate --prepare   # only when generated authority needs refresh/commit
./songchart candidate
```

Canonical closure after candidate PASS:

```bash
./songchart verify
```

The ergonomic closure wrapper may be used when the stage authority calls for generated refresh plus canonical verification:

```bash
./songchart close
```

Do not duplicate stage/canonical gates around these governed entrypoints.

## Exact closed HEAD rule

Canonical evidence is valid for the exact commit/tree that was verified. After canonical PASS:

1. confirm tracked working state is clean;
2. push that exact HEAD to the stage branch;
3. do not amend/rebase/add generated commits after canonical PASS without rerunning closure;
4. ensure the PR head SHA is the canonical-verified SHA before merge.

Required status checks and protected-branch controls remain GitHub-owned integration safeguards: https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches

## Release artifacts

`composer release:package` remains a post-canonical release/deployment artifact command. It is not a development handoff mechanism and must not be used to synchronize devices or working copies.

Release artifacts must never contain local secrets, `.env`, `.env.docker`, private certificates, database volumes, `vendor/`, `node_modules/`, logs, or mutable local runtime state unless an owning release contract explicitly requires a generated artifact.

## Corrective candidates

A same-stage correction remains on the current stage branch as a logical commit unless governance explicitly requires a separate revision. Re-run only the affected focused gates during iteration, then re-run exact-tree candidate/canonical closure before merge.

## Native Windows workflow

Native Batch/PowerShell development wrappers are retired. Windows development, when needed, uses WSL2/Linux with Docker and the same `./songchart` entrypoint as every other supported development environment.

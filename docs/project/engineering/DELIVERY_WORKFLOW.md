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

## Repository-wide convergence rule

Current typed/domain/machine contracts are the target state for the whole active repository, including legacy source, tests, fixtures, seeders and documentation when those surfaces are touched or exposed by verification.

- Do not preserve obsolete synonyms merely because historical code used them.
- Do not widen a current enum to accept a legacy value when the legacy value represented a different concept; migrate the consumer/fixture to the current owner instead.
- Provider `category`, operational `role` and `capability` are distinct. Legacy values such as role-like categories or provider-specific capability spellings must converge to the typed taxonomy.
- Repeated persisted machine states, reason codes and cross-boundary data shapes should consume their typed owner when one exists rather than repeat free-form strings.
- Overview Markdown must not preserve an older alternative to a newer canonical contract. Correct the overview or replace duplicated prose with an authority pointer.
- Historical migrations remain immutable even when legacy application/test vocabulary is normalized; persistence schema corrections still use governed forward migrations.
- A compatibility alias is permitted only when an explicit public/persisted compatibility contract requires it and the removal/migration strategy is documented.

When a broad gate exposes several legacy dialects of one semantic owner, inventory the whole affected vocabulary first and converge the group in one bounded corrective. Do not patch one failing literal at a time and repeatedly rerun the expensive closure lane.

## AI focus rule

AI/developers must resolve semantic ownership before implementation. The default question is not “what string makes the test pass?” but “which current authority owns this meaning?”

For architecture, persistence, provider, UI-state, workflow or release work:

1. read the current task and owning authority;
2. read `./songchart context --json` when the repository exposes the fact there;
3. prefer typed enums/contracts, registered factories/fixtures and generated context over reconstructed literals;
4. treat a mismatch between legacy source and current authority as convergence work, not evidence that the authority should become ambiguous;
5. keep the corrective bounded to the semantic owner and its registered consumers;
6. add or extend a permanent machine guard when the drift class can recur.

Do not create a second documentation truth source to make AI prompts shorter. Improve the existing authority, generated context, resolver or test fixture owner instead.

## Mobile / GitHub-native operating mode

GitHub is also the preferred control plane when the active operator is on a constrained device such as iPhone/iPad.

- A remote/GitHub writer may implement bounded source changes after one-writer synchronization is established.
- The mobile operator should normally perform short Git sync/verification commands rather than paste source code into Codespaces terminals.
- Exact commit SHA, GitHub Actions run state, PR checks and uploaded failure evidence are preferred handoff/debug evidence.
- PostgreSQL CI failures preserve `storage/logs/postgres-test-last-failure.log` as a short-retention GitHub Actions artifact named with the exact commit SHA and run attempt.
- Failure artifacts are diagnostic/runtime evidence only; they do not become tracked repository authority and must not contain secrets.
- GitHub Actions should automate deterministic verification/evidence transport that already has a repository owner. Do not move product/domain truth into workflow YAML.
- Prefer native branch protection, required checks, PR review status, concurrency cancellation, artifacts and releases over custom orchestration when those GitHub primitives satisfy the requirement.
- Do not reduce PR/main verification coverage merely to make mobile operation faster. Optimize iteration through focused local/remote gates and better evidence transport instead.

## Golden delivery workflow

```text
accepted main baseline
    ↓
stage branch
    ↓
planned impact + official/native capability review
    ↓
logical implementation source/contract/test changes
    ↓
Pint write mode on exact changed PHP paths
    ↓
Pint --test + verifier/Architecture ownership closure
    ↓
source/contract/consumer commit
    ↓
actual-diff impact + generated-authority reconcile
    ↓
generated-only commit when needed
    ↓
require docs/project/generated clean
    ↓
impact --verify fast preflight + collect-all audit/focused gates
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

## Source hygiene before commit

Formatter drift is source mutation, not verification evidence. Normalize it before creating the authoritative source commit whenever possible.

For changed PHP source:

1. run Pint in write mode on the exact changed PHP files;
2. inspect `git diff` and `git diff --check`;
3. run Pint `--test` after normalization;
4. if the change adds `scripts/verify-*.php` or `tests/Architecture/*.php`, register it under exactly one existing `verification-consumer-graph.json` ownership rule before broad verification;
5. run repository compiler/consumer ownership verification before the expensive quality lane;
6. if repository compiler fingerprints are stale, reconcile the owning source/contract inputs and regenerate derived authority before broad quality verification rather than treating the stale fingerprint as a downstream test defect;
7. commit the authoritative source/contract/test/consumer state;
8. only then run `./songchart reconcile` and commit expected generated outputs separately when needed;
9. require `git status --short -- docs/project/generated` to be empty before `./songchart impact --verify`.

Do not use broad quality verification to discover formatter drift, unowned verification consumers, stale repository compiler fingerprints, or uncommitted generated authority when cheap deterministic checks can fail first.

## Workflow authority synchronization

A workflow optimization, verification-sequence change, command semantic change, mutation-envelope change, handoff rule, parser/authority rule, or new failure-handling rule is incomplete until its owning documentation authority is updated in the same logical change.

Required rule:

```text
WORKFLOW / VERIFICATION MECHANISM CHANGE
        ↓
UPDATE OWNING .md AUTHORITY
        ↓
UPDATE MACHINE-READABLE CONTRACT / ROUTING WHEN APPLICABLE
        ↓
UPDATE OR ADD PERMANENT REGRESSION CONSUMER
        ↓
RUN FOCUSED GOVERNANCE VERIFICATION
```

- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md` owns the mandatory AI/developer execution model.
- This file owns Git delivery, synchronization, closure and corrective workflow semantics.
- Machine contracts and verifiers must consume those authorities rather than silently introducing a newer workflow only in code/tests.
- A script/test optimization without matching Markdown authority is workflow drift even when the code is technically correct.
- A Markdown-only workflow rule that is intended to be permanent should gain a machine-readable contract or regression consumer whenever practical.
- Verification consumers should assert owned behavior, structure and invariants rather than copy volatile external implementation literals. In particular, exact third-party GitHub Action SHAs belong in executable workflow YAML; tests/verifiers should require immutable 40-character SHA pinning and reject rolling tags without duplicating the current SHA value.
- Do not teach a new workflow only in chat, PR comments, generated context, or model-specific bootstrap files.

When a new failure class reveals a reusable workflow lesson, fix the immediate defect first, then promote the durable rule to its owning `.md` authority and permanent guard before considering the correction complete.

## Generated authority synchronization point

`./songchart reconcile` owns a `generated-only` mutation envelope. Its output is derived from the exact authoritative source tree and must not leak into candidate discovery as an uncommitted mutation.

After reconcile:

- inspect the generated diff and `git diff --check`;
- if only expected `docs/project/generated/` files changed, commit them as a generated-only commit;
- if generated output is unexpected, restore/fix the owning source or authority rather than hand-editing generated JSON;
- do not run `./songchart impact --verify`, audit-to-candidate handoff, or candidate while `docs/project/generated/` is dirty.

`./songchart impact --verify` must fail before its expensive quality lane when generated authority has uncommitted changes. Candidate remains a clean-tree closure guard, not the first detector for a known reconcile mutation.

## One writer per surface

During AI-assisted development, one writer owns a source surface until the next synchronization point.

- If an AI pushes a corrective commit that touches a file, the local workspace fetches and integrates that commit before making another edit to the same file.
- If the local branch has diverged after a rebase, do not keep writing competing remote commits to the same files. Prefer a bounded corrective commit and local cherry-pick, or finish the local integration first.
- Before changing writer, publish the current intended state as a commit SHA and run/review `./songchart impact --diff` when the handoff could expand the affected surface.
- Before a remote/GitHub writer resumes work, local-only commits on that same branch must be pushed or intentionally integrated. `git log --oneline @{upstream}..HEAD` should be empty at the synchronization point.
- A branch that is known behind or diverged from its upstream is not a valid remote-writer handoff point. Synchronize first; do not create another competing remote commit.
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
3. normalize changed PHP with Pint write mode and require Pint `--test` to pass;
4. ensure every new verifier/Architecture test has exactly one ownership rule;
5. commit the intended logical source state;
6. run `./songchart reconcile` when registered generated inputs changed and commit only expected generated outputs;
7. verify `docs/project/generated/` is clean;
8. push the current stage branch;
9. verify there are no local-only commits left before another writer starts;
10. communicate the exact commit SHA that owns the handoff;
11. on the next environment, fetch and fast-forward/rebase/cherry-pick only after reviewing divergence;
12. use `./songchart ai doctor` for runtime/test evidence that is not represented in Git.

Never use cross-device stash as a handoff mechanism.

## Verification during delivery

During iteration use the dependency-aware workflow:

```bash
./songchart impact <planned-paths...>
# implement
./songchart impact --diff
./songchart reconcile
# commit expected docs/project/generated changes
./songchart impact --verify
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

Release automation should prefer GitHub-native release/tag/check provenance once the accepted exact `main` SHA is known. GitHub Releases may transport governed release artifacts, but they do not replace SongChart candidate/canonical verification or the repository release package contract.

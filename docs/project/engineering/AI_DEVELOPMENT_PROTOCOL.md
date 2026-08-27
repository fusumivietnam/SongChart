# SongChart AI Development Protocol

Status: mandatory AI/developer workflow authority. The machine-readable companion is `docs/project/engineering/ai-development-contract.json`.

## 1. Read order

Before modifying SongChart:

1. read `PROJECT_AUTHORITY.md`;
2. read this protocol;
3. read the current stage task contract;
4. read the owning domain/module authority;
5. read `docs/project/engineering/DELIVERY_WORKFLOW.md` when work may be handed between AI/device/writer;
6. resolve planned change impact when changed paths are known.

Do not invent a parallel workflow in model-specific instruction files.

## 2. Research and preflight before implementation

Run/read:

```bash
./songchart ai doctor
./songchart context --json
./songchart impact <planned-path> [planned-path...]
```

Use the impact output to identify affected authorities, reverse verification consumers and required focused verification before writing code.

Before writing custom infrastructure, inspect whether Laravel, PHP, PostgreSQL, Git, Composer or an already approved package owns the capability. For dependency, framework, infrastructure, runtime, database, queue/cache, frontend-stack, package-ownership, verification, or delivery changes, record the applicable official source and native capability assessment in the task contract.

Official sources explain upstream behavior. SongChart repository authorities remain the source of truth for project-specific contracts.

For dependency, infrastructure, framework-capability, runtime, database, queue/cache, frontend-stack, or package-ownership changes, also read:

- `docs/project/stack/STACK_OVERVIEW.md`
- `docs/project/stack/CAPABILITY_OWNERSHIP.md`

## 2.1 Machine-readable project context

Before modifying architecture, persistence, Docker, providers, migrations, seeders, or verification infrastructure, run/read `./songchart context --json`.

- Treat generated context as repository fact derived from existing authorities; do not create a parallel hand-maintained truth source.
- Do not infer class names, schema ownership, Compose services, table presence, seeder FQCNs, or command availability when context provides them.
- If context and source/runtime disagree, report drift and fix it before feature work.
- Refresh committed source context after changing a registered context input.

## 3. During implementation

- Resolve registered invariants through `RepositoryContractResolver`; do not copy authority-sensitive literals.
- Generated repository manifests are derived artifacts; compile them from the exact tree.
- Do not weaken PHPStan/Larastan, Pint, Pest, PostgreSQL, CI or candidate/canonical gates.
- Do not widen PHPStan baselines for new work.
- Do not add another verifier when the invariant can be represented by an existing authority/resolver.
- Prefer focused verification while iterating. Do not run the full PostgreSQL suite or production frontend build reflexively after every small edit.
- Historical migrations listed in `docs/project/stack/migration-lifecycle-contract.json` are immutable. Schema corrections must be new guarded forward migrations; do not edit frozen history.
- Keep source, owning authority and focused regression coverage in the same logical change when they define one invariant.

## 3.1 Mandatory post-diff impact closure

Planned impact is not sufficient evidence after implementation. Before declaring an implementation slice complete, resolve the actual branch/working-tree diff:

```bash
./songchart impact --diff
```

The actual-diff lane must include committed stage changes relative to the main integration base, current tracked working changes and untracked files. Reconcile any newly surfaced authority, reverse consumer or focused check before candidate closure.

If actual impact differs from planned impact, record the deviation in the task contract or current-stage state. Do not hide newly affected consumers just because they were not in the original plan.

## 3.2 Generated authority reconcile

After changing registered authority/context inputs, run:

```bash
./songchart reconcile
```

`reconcile` regenerates committed generated authority from the current exact tree and shows the generated diff. It does not automatically commit. Review the diff, then commit only expected generated outputs.

Never hand-merge derived generated JSON when it can be regenerated from final authoritative source.

## 3.3 Collect-all audit

Use:

```bash
./songchart audit
```

when broad repository feedback is useful before candidate closure.

Audit runs the quality/static/governance command set without fail-fast so multiple independent failures can be fixed in one iteration. Audit is diagnostic only:

- it must not call stage/canonical closure;
- it must not call the full PostgreSQL suite as a nested closure;
- it must not call the production frontend build as a nested closure;
- it does not replace fail-fast `quality:verify`, candidate or canonical verification.

## 3.4 Linux/Docker development runtime

Linux or WSL2 with Docker Engine + Compose v2 is the sole development, test and canonical host workflow. GitHub Codespaces is the preferred remote adapter. Use the repository `./songchart` CLI instead of host PHP/Composer/Node/PostgreSQL/Redis commands:

```bash
./songchart dev ready
./songchart artisan migrate
./songchart composer install
./songchart npm run build
./songchart dev test tests/Feature/...
./songchart candidate
./songchart verify
```

Native Windows Batch/PowerShell entrypoints and Laragon runtime compatibility are retired. Windows development, if needed, runs through WSL2 and the same Linux `./songchart` entrypoint.

## 3.5 Git delivery and AI/device handoff

Read `docs/project/engineering/DELIVERY_WORKFLOW.md` before handing off or closing any implementation stage.

- GitHub is the source of truth.
- Stage work is committed and pushed on the current stage branch.
- Device/AI handoff uses Git state plus `./songchart ai status` / `./songchart ai doctor` evidence.
- Use one writer per overlapping source surface until a synchronization point.
- When switching writer, communicate the exact commit SHA and integrate it before editing the same files again.
- If a local branch has diverged after rebase, stop competing remote/local writes to the same files. Prefer one bounded corrective commit plus cherry-pick, or finish local integration first.
- Generated-only historical commits that conflict during rebase may be skipped/dropped when their output will be regenerated from the final exact tree.
- After replaying historical PHP source in rebase, rerun impact-selected Pint/PHPStan/focused tests.
- Do not copy source ZIPs, incremental ZIPs, patch installers, or cross-device stashes for normal development handoff.
- `composer release:package` is post-canonical release packaging only; it is not a source synchronization mechanism.

## 3.6 Verification command surface

Use the public workflow entrypoints:

```text
./songchart impact <paths...>
./songchart impact --diff
./songchart reconcile
./songchart audit
./songchart dev test <path>
./songchart test
./songchart candidate
composer stage:verify
./songchart verify
./songchart close
composer canonical:verify
composer release:package
```

Do not resurrect removed aliases (`verify`, `release:verify`, `test:all`, `test:postgres-clean`, `delivery:verify`, `release-contract:verify`) in active code, docs, installers, tests, impact maps, or AI instructions. Historical stage documents may preserve old commands as history only.

### Migration lifecycle

For schema changes:

- inspect `docs/project/stack/migration-lifecycle-contract.json`;
- never edit a frozen historical migration;
- frozen history is compared by semantic fingerprint; formatting-only normalization is allowed, semantic schema/code changes are not;
- never bootstrap migration-history fingerprints from a packaging-side source artifact;
- add a forward migration with explicit existence guards when repairing prior schema drift;
- verify both fresh PostgreSQL installation and previous-schema → current upgrade behavior;
- use `composer migration-lifecycle:verify` for static history ownership and `composer migration-upgrade:verify` only inside the isolated PostgreSQL verification environment.

### Verification consumer graph

`docs/project/engineering/verification-consumer-graph.json` is the routing authority for verification consumers.

Before changing an authority or verifier:

1. resolve planned repository impact;
2. identify the semantic authority owner;
3. reconcile every registered verifier/Architecture consumer;
4. implement the change;
5. resolve actual-diff impact;
6. extend an existing resolver/authority instead of copying literals;
7. require repository compiler closure before stage verification.

Every `scripts/verify-*.php` file and every `tests/Architecture/*.php` file must match exactly one ownership rule. A new consumer without an owner, a consumer matching multiple rules, or a verifier without a Composer execution owner is a repository-contract failure.

Architecture tests verify boundaries and ownership. They must not independently redefine authority-sensitive command order, schema placement, package fields, or migration-history literals already owned by machine contracts.

### Impact map validity

`docs/project/stack/impact-test-map.json` is executable routing authority, not documentation prose.

- Every referenced Composer command must exist in current `composer.json`.
- Removed aliases are forbidden.
- Referenced test files/directories/globs must resolve.
- When a route becomes stale, update the impact map in the same correction instead of teaching AI/developers a dead command.

### Runtime/generated artifact ownership

Laravel/runtime storage such as `storage/framework/**`, `storage/logs/**` and local backup/runtime paths must not be Git-tracked unless an explicit committed-generated authority says otherwise.

Canonical may generate runtime evidence/snapshots, but it must not mutate tracked runtime files before candidate evidence is recorded. Committed generated repository authority belongs under the explicitly governed generated documentation surface and is regenerated from source.

### Authorization authority

Authorization is Laravel Gate-first. Read `docs/project/security/authorization-contract.json` before changing privileged access.

- `Capability` defines stable Gate names.
- `AuthorizationMatrix` loads the static role→capability matrix once.
- `UserRole` identifies roles; it must not own capability methods.
- `User` owns authentication state/model concerns; it must not duplicate capability methods.
- Controllers, services, commands and views authorize through Laravel Gates.
- Direct `UserRole::SuperAdmin` checks are permitted only for explicit business invariants such as protecting the last active super administrator, not as general authorization.
- Do not add Spatie Permission unless a later task demonstrates a real dynamic/custom-role/tenant-RBAC requirement.

### Removed-symbol and exact-target closure

For refactors that remove or rename a method, class, Gate implementation, middleware, command, or file:

- scan the exact target tree for the removed symbol before candidate closure;
- scan for orphan files that still depend on the removed symbol;
- do not treat a reconstructed or packaging-side source tree as proof that all target consumers were found;
- add the removed surface to its owning machine authority when the absence is a durable invariant;
- run the owning static verifier before relying on PHPStan to discover stale consumers one by one.

### Application data boundary

Read `docs/project/domain/application-data-boundary.json` before adding persistence access.

- HTTP controllers are transport adapters and must not open transactions, call query builders directly, or persist models.
- Read composition belongs in Application Query/Read Model surfaces. Read models may use optimized Eloquent or Query Builder and are not forced behind repository wrappers.
- Registered read models are mutation-free.
- Writes belong in Application Actions/Commands or explicit domain/support write services and may own transactions, locking and persistence invariants.
- Repository abstractions are required only where they express a real domain/storage boundary; do not wrap trivial reads just to satisfy layering.
- Query budgets are registered in `docs/project/performance/query-budget-contract.json`. Hard limits must be calibrated on representative PostgreSQL fixtures rather than guessed.
- When a new read model is added, register it in the application-data-boundary authority and impact graph in the same change.

## 4. Verification lifecycle — golden path

```text
RESEARCH / ORIENT
  ai doctor + context + current task/authority + official/native capability review
        ↓
PLAN
  ./songchart impact <planned paths>
        ↓
IMPLEMENT
  smallest coherent source + authority + regression slice
        ↓
POST-DIFF
  ./songchart impact --diff
        ↓
RECONCILE
  ./songchart reconcile when generated inputs changed
        ↓
AUDIT
  ./songchart audit for collect-all static/governance feedback
        ↓
FOCUSED VERIFY
  impact-selected Pint/PHPStan/verifiers/tests
        ↓
CANDIDATE
  ./songchart candidate [--prepare]
        ↓
CANONICAL
  ./songchart verify or governed ./songchart close
        ↓
DELIVERY
  push exact closed HEAD → PR CI → merge → optional package/tag
```

### Focused lane

During implementation: run only affected contract verifiers, Pint/PHPStan for changed PHP surfaces where practical, and focused Unit/Architecture/Feature tests.

### Stage closure

Run exactly:

```bash
composer stage:verify
```

or through the governed exact-tree wrapper:

```bash
./songchart candidate
```

### Canonical closure

After candidate PASS, run:

```bash
./songchart verify
```

or the governed close wrapper when the task calls for generated refresh plus canonical closure:

```bash
./songchart close
```

The canonical Docker shell installs locked dependencies, normalizes with the locked formatter, then invokes exactly `composer canonical:verify`. `canonical:verify` calls `stage:verify` once and owns the remaining runtime/package/migration/evidence gates.

After canonical PASS, do not amend/rebase/change the verified tree before merge without rerunning closure. Push the exact closed HEAD and verify the PR head SHA matches it.

### Packaging

Only after canonical PASS and exact-tree provenance PASS:

```bash
composer release:package
```

## 5. Failure handling

When a gate fails:

1. preserve the exact assertion/SQLSTATE/static-analysis evidence;
2. identify the authority that owns the invariant;
3. inspect whether the failure is source behavior, stale consumer, stale generated authority, dead impact command, or runtime-artifact ownership;
4. correct the authority/resolver/consumer rather than adding a parallel literal;
5. add a regression ledger entry only when a permanent machine guard exists;
6. keep the correction on the current stage branch unless a separate revision is explicitly required;
7. rerun actual-diff impact after the correction expands the changed surface;
8. do not use `--force` as normal recovery.

## 6. Verification budget

Do not trade correctness for speed. Reduce duplicated execution, not gate coverage.

- documentation-only change: documentation/authority focused gates;
- PHP implementation: planned impact → focused Pint/PHPStan + focused tests → actual-diff impact;
- schema/package change: add PostgreSQL/package-focused gates;
- broad static/governance diagnosis: `./songchart audit`;
- candidate closure: `composer stage:verify` / `./songchart candidate`;
- release closure: canonical Docker → `composer canonical:verify` / `./songchart verify` or `./songchart close`.

Evidence caching/reuse, sophisticated CI path pruning and merge-queue adoption remain deferred until this topology demonstrates stable dependency resolution and exact-tree closure. Correctness and diagnosability come before execution caching.

## 7. Model-specific bootstrap files

`AGENTS.md`, `CLAUDE.md` and `GEMINI.md` may contain only model/bootstrap compatibility notes and pointers to canonical authorities. Product, verification and implementation rules belong here or in their owning authority, not copied into three model-specific files.

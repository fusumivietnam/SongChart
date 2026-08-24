# SongChart AI Development Protocol

Status: mandatory AI/developer workflow authority. The machine-readable companion is `docs/project/engineering/ai-development-contract.json`.

## 1. Read order

Before modifying SongChart:

1. read `PROJECT_AUTHORITY.md`;
2. read this protocol;
3. read the current stage task contract;
4. read the owning domain/module authority;
5. resolve change impact when planned paths are known.

Do not invent a parallel workflow in model-specific instruction files.

## 2. Before implementation

Run:

```bash
php scripts/resolve-repository-impact.php <changed-path> [changed-path...]
```

Use the output to identify affected authorities, registered consumers and required focused verification.

Before writing custom infrastructure, inspect whether Laravel, PHP, PostgreSQL or an already approved package owns the capability.

For dependency, infrastructure, framework-capability, runtime, database, queue/cache, frontend-stack, or package-ownership changes, also read:

- `docs/project/stack/STACK_OVERVIEW.md`
- `docs/project/stack/CAPABILITY_OWNERSHIP.md`


## 2.1 Machine-readable project context

Before modifying architecture, persistence, Docker, providers, migrations, seeders, or verification infrastructure, run/read `songchart context --json`.

- Treat generated context as repository fact derived from existing authorities; do not create a parallel hand-maintained truth source.
- Do not infer class names, schema ownership, Compose services, table presence, seeder FQCNs, or command availability when context provides them.
- If context and source/runtime disagree, report drift and fix it before feature work.
- Refresh the committed source manifest with `songchart context --refresh-source` after changing a registered context input.

## 3. During implementation

- Resolve registered invariants through `RepositoryContractResolver`; do not copy authority-sensitive literals.
- Generated repository manifests are derived artifacts; compile them from the exact tree.
- Do not weaken PHPStan/Larastan, Pint, Pest, PostgreSQL, CI or candidate gates.
- Do not widen PHPStan baselines for new work.
- Do not add another verifier when the invariant can be represented by an existing authority/resolver.
- Prefer focused verification while iterating. Do not run the full PostgreSQL suite or production frontend build reflexively after every small edit.
- Historical migrations listed in `docs/project/stack/migration-lifecycle-contract.json` are immutable. Schema corrections must be new guarded forward migrations; do not edit frozen history.

## 3.1 Docker-first development runtime

Docker Desktop with WSL2 is the primary development, test and canonical runtime. Use the repository `songchart` CLI instead of host PHP/Composer/Node/PostgreSQL/Redis commands:

```bat
songchart dev up
songchart artisan migrate
songchart composer install
songchart npm run build
songchart test
songchart verify
```

Laragon is compatibility-only. Do not make release/canonical claims from Laragon and do not add new Laragon-specific workflow branches unless a compatibility task explicitly owns them.


## 3.1.1 Delivery artifacts

Read `docs/project/engineering/DELIVERY_WORKFLOW.md` before packaging or handing off any implementation stage.

For every stage/corrective candidate, produce both artifacts from the same exact tree:

- full Laragon-ready source ZIP;
- incremental changeset ZIP from the last accepted/canonical baseline.

Prefer the changeset for normal upgrades. Never overwrite local `.env`. A changeset must include a baseline declaration, changed/added files, `REMOVE_FILES.txt`, backup-before-mutation behavior, and Docker-first verification instructions. Do not substitute host `composer stage:verify` for the repository `.bat`/Docker entrypoint.

When the user's target baseline is known, do not ask them to re-extract the full source merely to apply a normal stage upgrade.

## 3.2 Verification command surface

Use only the active workflow entrypoints:

```text
songchart test
composer stage:verify
songchart verify
composer canonical:verify
composer release:package
```

Do not resurrect removed aliases (`verify`, `release:verify`, `test:all`, `test:postgres-clean`, `delivery:verify`, `release-contract:verify`) in code, docs, installers, tests, or AI instructions. Historical stage documents may preserve old commands as history only.


### Migration lifecycle

For schema changes:

- inspect `docs/project/stack/migration-lifecycle-contract.json`;
- never edit a frozen historical migration;
- frozen history is compared by semantic fingerprint; formatting-only normalization is allowed, semantic schema/code changes are not;
- Never bootstrap migration-history fingerprints from a packaging-side source artifact. The baseline is sealed once from the exact canonical Docker target.
- add a forward migration with explicit existence guards when repairing prior schema drift;
- verify both fresh PostgreSQL installation and previous-schema â†’ current upgrade behavior;
- use `composer migration-lifecycle:verify` for static history ownership and `composer migration-upgrade:verify` only inside the isolated PostgreSQL verification environment.


### Verification consumer graph

`docs/project/engineering/verification-consumer-graph.json` is the routing authority for verification consumers.

Before changing an authority or verifier:

1. resolve repository impact;
2. identify the semantic authority owner;
3. reconcile every registered verifier/Architecture consumer;
4. extend an existing resolver/authority instead of copying literals;
5. require repository compiler closure before stage verification.

Every `scripts/verify-*.php` file and every `tests/Architecture/*.php` file must match exactly one ownership rule. A new consumer without an owner, a consumer matching multiple rules, or a verifier without a Composer execution owner is a repository-contract failure.

Architecture tests verify boundaries and ownership. They must not independently redefine authority-sensitive command order, schema placement, package fields, or migration-history literals already owned by machine contracts.


### Authorization authority

Authorization is Laravel Gate-first. Read `docs/project/security/authorization-contract.json` before changing privileged access.

- `Capability` defines stable Gate names.
- `AuthorizationMatrix` loads the static roleâ†’capability matrix once.
- `UserRole` identifies roles; it must not own capability methods.
- `User` owns authentication state/model concerns; it must not duplicate capability methods.
- Controllers, services, commands and views authorize through Laravel Gates.
- Direct `UserRole::SuperAdmin` checks are permitted only for explicit business invariants such as protecting the last active super administrator, not as general authorization.
- Do not add Spatie Permission unless a later task demonstrates a real dynamic/custom-role/tenant-RBAC requirement.


### Removed-symbol and exact-target closure

For refactors that remove or rename a method, class, Gate implementation, middleware, command, or file:

- scan the exact target tree for the removed symbol before candidate closure;
- scan for orphan files that still depend on the removed symbol;
- do not treat a packaging-side reconstructed source tree as proof that all target consumers were found;
- add the removed surface to its owning machine authority when the absence is a durable invariant;
- run the owning static verifier before relying on PHPStan to discover stale consumers one by one.

A changeset may explicitly retire a known orphan target file even when that file is absent from a pre-canonical packaging artifact; the installer must back it up before removal.


### Application data boundary

Read `docs/project/domain/application-data-boundary.json` before adding persistence access.

- HTTP controllers are transport adapters and must not open transactions, call query builders directly, or persist models.
- Read composition belongs in Application Query/Read Model surfaces. Read models may use optimized Eloquent or Query Builder and are not forced behind repository wrappers.
- Registered read models are mutation-free.
- Writes belong in Application Actions/Commands or explicit domain/support write services and may own transactions, locking and persistence invariants.
- Repository abstractions are required only where they express a real domain/storage boundary; do not wrap trivial reads just to satisfy layering.
- Query budgets are registered in `docs/project/performance/query-budget-contract.json`. Hard limits must be calibrated on representative PostgreSQL fixtures rather than guessed.
- When a new read model is added, register it in the application-data-boundary authority and impact graph in the same change.

## 4. Verification lifecycle

### Impact lane

Before implementation: resolve affected authorities and consumers.

### Focused lane

During implementation: run only affected contract verifiers, Pint/PHPStan for changed PHP surfaces where practical, and focused Unit/Architecture/Feature tests.

### Stage closure

Run exactly:

```bash
composer stage:verify
```

This owns repository-wide quality/static closure, the authoritative PostgreSQL suite and frontend production build for the exact candidate tree.

### Canonical closure

The canonical Docker shell installs locked dependencies, normalizes with the locked formatter, then invokes exactly:

```bash
composer canonical:verify
```

`canonical:verify` calls `stage:verify` once and owns the remaining runtime/package/migration/evidence gates. Do not separately rerun `quality:verify`, `test:postgres` or `npm run build` around it.

### Packaging

Only after canonical PASS and exact-tree provenance PASS:

```bash
composer release:package
```

## 5. Failure handling

When a gate fails:

1. preserve the exact assertion/SQLSTATE/static-analysis evidence;
2. identify the authority that owns the invariant;
3. correct the authority/resolver/consumer rather than adding a parallel literal;
4. add a regression ledger entry only when a permanent machine guard exists;
5. produce a same-stage corrective candidate;
6. do not use `--force` as normal recovery.

## 6. Verification budget

Do not trade correctness for speed. Reduce duplicated execution, not gate coverage.

- documentation-only change: documentation/authority focused gates;
- PHP implementation: focused Pint/PHPStan + focused tests;
- schema/package change: add PostgreSQL/package-focused gates;
- candidate closure: `composer stage:verify`;
- release closure: canonical Docker â†’ `composer canonical:verify`.

Evidence caching/reuse is intentionally deferred until this topology is stable.

## 7. Model-specific bootstrap files

`AGENTS.md`, `CLAUDE.md` and `GEMINI.md` may contain only model/bootstrap compatibility notes and pointers to canonical authorities. Product, verification and implementation rules belong here or in their owning authority, not copied into three model-specific files.

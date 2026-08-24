# Stage 16.5.3 Validation Report — Verification Workflow Consolidation & AI Development Protocol

Status: implementation candidate v21; canonical closure pending.

## Implemented

- machine-readable verification topology with impact/focused/quality/stage/canonical lanes;
- `composer stage:verify` as one candidate closure owner;
- `composer canonical:verify` as one canonical closure owner;
- `release:verify` and `verify` reduced to compatibility aliases;
- canonical shell reduced to environment/dependency preparation + one canonical entrypoint;
- verification topology machine guard;
- single AI development protocol authority + machine-readable contract;
- AGENTS/CLAUDE/GEMINI reduced to thin bootstraps;
- AI protocol machine guard;
- task template gains an explicit verification plan/budget;
- historical pre-roadmap Stage 16.5.3 governance records preserved as legacy files;
- verification evidence reuse intentionally deferred until topology stability is proven.

## Packaging environment limitations

This implementation-source environment does not own the target lockfiles/vendor/node_modules/Docker/PostgreSQL runtime. Packaging-side checks therefore do not claim Pint, PHPStan, Pest, PostgreSQL, frontend production build or canonical closure.

## Closure rule

Stage 16.5.3 closes only after target `composer stage:verify` and canonical Docker `composer canonical:verify` pass on the exact tree, candidate evidence is recorded, and provenance remains valid.


## Packaging-side evidence

Repository-local syntax and static/governance verification passed for:
- verification topology;
- AI development protocol;
- executable repository compiler;
- regression ledger (20 guarded classes);
- package/model schema contracts;
- DB test isolation/runtime contracts;
- release orchestration/performance baseline;
- package/test/candidate/engineering governance;
- documentation/repository state/schema ownership/impact map;
- official-source/authority dependency/code-generation/database/runtime authority;
- CI/Laravel alignment/source package.

The packaging baseline does not contain the target lockfiles/vendor/node_modules/Docker/PostgreSQL runtime, so canonical closure remains target-only.


## v21.1 corrective — reproducible environment verifier consumes consolidated topology

The first v21 canonical run reached `reproducible-environment:verify`, which still searched `scripts/canonical-verify.sh` for the legacy `composer release:verify` literal.

That assumption became stale when Stage 16.5.3 deliberately moved canonical ownership to `composer canonical:verify`.

v21.1 updates the reproducible-environment verifier to:
- require locked Pint normalization before `composer canonical:verify`;
- read `verification-topology.json` for the canonical shell owner;
- require exactly one canonical entrypoint;
- reject duplicated `release:verify`, `stage:verify`, `quality:verify`, `test:postgres`, or frontend build calls in the shell.

Regression `REG-021 legacy-verifier-topology-assumption` records this class.


## v21.2 corrective — local data safety consumes canonical/stage topology

The v21.1 canonical run reached `local-data-safety:verify`, which still expected `test-database:safety` to exist directly inside the legacy `release:verify` array.

Stage 16.5.3 changed that ownership intentionally:
- `release:verify` is a compatibility alias to `canonical:verify`;
- `canonical:verify` runs `@test-database:safety` before `@stage:verify`;
- `stage:verify` owns the destructive `@test:postgres` lane.

v21.2 updates local-data-safety verification to resolve these two ordered pipelines from `verification-topology.json` and enforce the cross-lane ordering rather than the superseded alias shape.

Regression `REG-022 local-data-safety-legacy-release-assumption` records this class.


## v21.3 corrective — stack review belongs to AI protocol, not AGENTS bootstrap

The v21.2 canonical run reached `stack:verify`. Its only failure was a pre-consolidation assumption that `AGENTS.md` must directly contain `STACK_OVERVIEW.md` and `CAPABILITY_OWNERSHIP.md`.

That conflicts with Stage 16.5.3's explicit thin-bootstrap rule.

v21.3 moves the stack-review obligation to the canonical AI workflow authority:
- `AI_DEVELOPMENT_PROTOCOL.md` conditionally requires stack overview/capability ownership review for dependency/infrastructure/runtime/framework/database/queue/cache/frontend/package-ownership changes;
- `ai-development-contract.json` machine-encodes the same rule;
- `verify-stack-baseline.php` verifies those authorities and only requires `AGENTS.md` to point to the AI protocol.

Regression `REG-023 thin-bootstrap-stack-authority-assumption` records this class.


## v21.4 corrective — foundation closure consumes stage topology

The v21.3 canonical run reached `foundation:audit`, which still treated Composer `verify` as a full pipeline and required it to contain `@quality:verify`, `@test:all`, and `npm run build`.

Stage 16.5.3 intentionally changed that ownership:
- `verify` is a compatibility alias to `@stage:verify`;
- `stage:verify` owns quality, authoritative PostgreSQL tests, and frontend build;
- `test:all` itself is only an alias to the PostgreSQL authority.

v21.4 updates foundation closure to verify the alias and the `stage:verify` pipeline against `verification-topology.json`, rather than requiring duplicated commands in `verify`.

Regression `REG-024 foundation-legacy-verify-pipeline-assumption` records this class.


## v21.5 corrective — failing PostgreSQL test evidence is printed last

The v21.4 canonical run passed static/topology closure and reached the real PostgreSQL test lane. PostgreSQL state was healthy, but the shared terminal excerpt contained only the DB diagnostic and Composer failure chain.

The runner already captured the exact Laravel/Pest output, but printed it before DB diagnostics. v21.5 keeps the same test behavior and changes evidence presentation only:

- captured output remains redacted and persisted at `storage/logs/postgres-test-last-failure.log`;
- DB diagnostics run normally;
- a 200-line exact Pest/Laravel tail is printed **after all diagnostics**, bounded by `=== COPY FROM HERE ===` / `=== END COPY ===`.

No application/test behavior is changed until the actual failing test/exception is known.

Regression `REG-025 database-test-evidence-tail-ordering` records this failure-evidence class.


## v21.6 corrective — migrate all stale Architecture verification assertions

The v21.5 canonical PostgreSQL lane provided complete evidence: 270 tests passed and exactly 10 Architecture assertions failed. All failures were pre-16.5.3 workflow assumptions; there was no PostgreSQL, migration, Feature-test, or application behavior failure.

v21.6 migrates those Architecture tests to the executable verification topology:
- `verify` is asserted as the compatibility alias `@stage:verify`;
- `release:verify` is asserted as the compatibility alias `@canonical:verify`;
- stage ownership is checked against `release-pipeline.stage_steps` / `verification-topology.lanes.stage.ordered_steps`;
- canonical ownership is checked against `canonical_steps`;
- database safety ordering is checked as canonical safety before stage;
- PostgreSQL authority is checked inside stage;
- formatter ordering is checked before `composer canonical:verify`;
- removed `preflight_order` assertions are replaced with the current stage/canonical topology rules.

`verify-verification-topology.php` now rejects stale Architecture-test patterns that try to assert nested copied `verify`/`release:verify` pipelines or the removed `preflight_order`.

Regression `REG-026 architecture-test-verification-topology-drift` records this class.

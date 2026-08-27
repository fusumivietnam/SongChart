# Stage 18.3.1 Task Contract — Verification & AI Workflow Convergence

Status: active task contract.

## Goal

Converge SongChart research, analysis, implementation, testing, quality, candidate, canonical, delivery and AI handoff into one dependency-aware golden path that surfaces impacted authorities/consumers early, keeps generated/runtime artifacts correctly owned, and preserves exact-tree release confidence without weakening existing gates.

## Non-goals

- No product feature work, provider breadth, schema redesign, ingestion redesign, frontend redesign, or Admin feature expansion.
- No verification evidence caching/reuse.
- No merge queue requirement.
- No aggressive CI path-pruning that reduces PR/main coverage.
- No new workflow framework or parallel source-of-truth outside `./songchart`, Composer scripts, and existing repository authorities.
- No weakening Pint, PHPStan/Larastan, Pest, PostgreSQL, CI, candidate, canonical, migration, package, or release gates.

## Acceptance criteria

- Planned paths and actual Git diff can both be resolved to impacted authorities, registered reverse consumers and focused verification requirements.
- The public workflow exposes dependency-aware `impact`, generated-authority `reconcile`, and collect-all diagnostic `audit` entrypoints through `./songchart`.
- Impact-map verification rejects retired/nonexistent Composer commands and missing test targets instead of silently routing AI/developers to dead commands.
- Runtime/generated files intentionally mutated by canonical verification cannot remain tracked unless explicitly declared as committed generated authority.
- AI/developer delivery authority defines one-writer-per-surface synchronization, safe rebase/cherry-pick handoff, exact-closed-HEAD push, and generated-artifact conflict regeneration.
- AI protocol requires official/native capability research when applicable, planned impact before implementation, post-diff impact after implementation, reconcile, audit, focused verification, candidate, canonical, then delivery.
- Task template records official sources, native capability assessment, planned impact, post-diff deviations, and verification plan.
- Existing quality/candidate/canonical topology remains authoritative and exact-tree canonical closure still owns stage verification exactly once.

## Affected modules and boundaries

- `songchart` Linux/Docker CLI.
- Repository contract impact resolution.
- Verification impact-map validation.
- Runtime/generated artifact ownership verification.
- AI development and delivery documentation authorities.
- Verification consumer graph / repository compiler registrations where required.
- Development state and validation evidence.

## Expected files

- `songchart`
- `scripts/resolve-repository-impact.php`
- `scripts/run-workflow-audit.php`
- `scripts/verify-impact-test-map.php`
- `scripts/verify-runtime-artifact-ownership.php`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/stack/impact-test-map.json` when stale aliases are confirmed
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `tests/Architecture/Stage1831WorkflowConvergenceTest.php`
- `docs/project/DEVELOPMENT_STATE.md`
- `docs/foundation/STAGE_18_3_1_VALIDATION_REPORT.md`
- generated repository authority refreshed from the exact final tree during closure.

## Allowed incidental files

- `docs/project/generated/**` generated authority outputs.
- formatter-only changes required by locked Pint.
- candidate runtime evidence under ignored runtime storage.

## Scope deviations

- None at stage initialization. Any additional source path must be recorded with owner, reason and focused verification.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Git | https://git-scm.com/docs/git-rebase | Rebase replay/conflict/continue/skip/abort semantics | 2026-08-28 |
| GitHub | https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches | Required status checks and protected-branch delivery controls | 2026-08-28 |
| Composer | https://getcomposer.org/doc/articles/scripts.md | Root-package scripts, ordered execution and script reuse | 2026-08-28 |
| PHPStan | https://phpstan.org/writing-php-code/phpdoc-types | Explicit iterable/value/generic PHPDoc typing | 2026-08-28 |
| Laravel | https://laravel.com/docs/13.x/structure | `storage/framework` as generated framework/runtime storage | 2026-08-28 |

Official docs support platform/framework behavior. SongChart repository authorities remain authoritative for project-specific workflow and release rules.

### Native capability assessment

- Capability owner: Git + Docker Compose + Composer + existing SongChart repository resolver/CLI.
- Native/first-party capability available: partial.
- Selected primitives: Git diff/status/rebase semantics, Composer script execution, Docker-isolated runtime, Laravel storage conventions.
- Why they satisfy the requirement: SongChart needs only a thin orchestration layer over existing primitives plus project-specific authority graph resolution.

### Custom implementation justification

- Custom code required: yes, narrow.
- Missing official behavior: no upstream tool understands SongChart semantic authority ownership, reverse verification consumers, impact-test-map routing, or exact-tree generated authority.
- Narrow custom boundary: dependency resolution/reporting and orchestration only.
- Framework primitives reused: Git, Composer, Docker Compose, PHP/Pest, existing `RepositoryContractResolver`.
- Non-goals: custom CI engine, custom VCS, workflow DSL, evidence cache.

## Domain contract and use-case data surface

- No product/domain data mutation.
- No route/API contract changes.
- No canonical database writes.
- Workflow reads repository source, Git state and machine-readable engineering authorities only.

## Security, authorization, and data impact

- No application authorization change.
- Diagnostics must remain secret-redacted and must not print `.env` values, credentials or runtime secrets.
- Runtime artifact verification uses Git tracked/ignored metadata only.

## Verification plan

- Impact lane: planned paths first; actual `git diff` again after implementation.
- Focused implementation gates: architecture tests for workflow convergence; impact-map verifier; runtime artifact ownership verifier; AI protocol and repository-compiler verifiers; Pint/PHPStan for changed PHP.
- Diagnostic lane: `./songchart audit` collects all quality/static/governance failures without changing fail-fast quality/canonical semantics.
- Stage closure owner: `composer stage:verify` / `./songchart candidate`.
- Canonical closure owner: `composer canonical:verify` / `./songchart close`.
- Packaging owner: `composer release:package` only after canonical acceptance.
- Explicitly avoided duplicate/nested gates: audit must not invoke stage/canonical/full PostgreSQL/frontend build as nested closure.

## Tests and verification

- Add durable Architecture coverage for public workflow commands and ownership invariants.
- Verify post-diff impact includes untracked files and reverse consumers where registered.
- Verify stale Composer aliases are rejected by impact-map governance.
- Verify ignored runtime storage cannot be tracked accidentally.
- Run locked Pint and PHPStan/Larastan.
- Run focused workflow tests before candidate/canonical.
- Full PostgreSQL/build remain stage/canonical owners, not iteration defaults.

## Documentation impact

- Update AI protocol and machine companion.
- Update delivery workflow.
- Update task template.
- Update development state and final validation report.
- Keep model bootstrap files pointer-only; do not duplicate workflow rules into `AGENTS.md`, `CLAUDE.md`, or `GEMINI.md`.

## Rollback

- Revert Stage 18.3.1 commits; no schema/data rollback required.
- Existing `quality:verify`, candidate and canonical entrypoints remain functional throughout implementation.

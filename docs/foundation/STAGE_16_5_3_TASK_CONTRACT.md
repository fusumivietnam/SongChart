# Stage 16.5.3 — Verification Workflow Consolidation & AI Development Protocol

Status: implementation candidate.

## Goal

Consolidate SongChart verification into one non-duplicative execution topology and one AI development protocol authority so correctness gates remain strict without repeated nested execution or model-specific instruction drift.

## Non-goals

- reducing gate coverage;
- weakening PHPStan, Pint, Pest, PostgreSQL, package, CI or candidate checks;
- adding verification evidence caching before topology is stable;
- changing product/domain behavior;
- dependency upgrades.

## Acceptance criteria

- `quality:verify` owns static/governance closure only.
- `stage:verify` owns quality + authoritative PostgreSQL suite + frontend production build exactly once.
- `canonical:verify` owns canonical runtime/package/migration/evidence closure and invokes `stage:verify` exactly once.
- `release:verify` remains compatibility alias only; it does not copy the pipeline.
- canonical shell prepares locked dependencies/formatter and calls `composer canonical:verify` exactly once.
- installer delegates to `stage:verify` / `canonical:verify` rather than calling Pint/PHPStan/tests/build directly.
- one machine-readable verification topology is executable and guarded.
- one AI protocol authority is executable and guarded.
- AGENTS/CLAUDE/GEMINI remain thin bootstrap pointers.
- verification evidence caching is explicitly deferred.

## Affected modules and boundaries

Engineering/repository governance only: Composer scripts, canonical orchestration, installer workflow, AI/project instruction authorities, task templates, architecture tests and executable repository authority graph.

## Changed authorities

- `verification-topology`
- `ai-development-protocol`

## Expected files

- `composer.json`
- `scripts/canonical-verify.sh`
- `scripts/verify-verification-topology.php`
- `scripts/verify-ai-development-protocol.php`
- `scripts/verify-performance-baseline.php`
- `scripts/verify-release-orchestration.php`
- `scripts/verify-database-authority.php`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/generated/repository-contract-manifest.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/stack/package-registry.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/engineering/regression-ledger.json`
- `tests/Architecture/VerificationWorkflowTopologyTest.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_5_3_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_5_3_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_5_3_LEGACY_CONTRACT_COVERAGE_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_5_3_LEGACY_CONTRACT_COVERAGE_VALIDATION_REPORT.md`
- `candidate-verification.json`

## Allowed incidental files

- formatter-only changes to listed files;
- generated exact-tree repository manifest;
- changeset/release metadata outside repository.

## Scope deviations

None planned.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/stack/release-pipeline-contract.json`
- Stage 16.5.2 canonical baseline.

### Installed versions

No dependency changes. Existing PHP 8.5 / Laravel 13 / PostgreSQL 18 / Node 22 / locked Composer/npm authorities remain unchanged.

### Official external sources

No new external capability is adopted by this stage.

### Native capability assessment

Composer scripts remain execution adapters; PHP repository verifiers own SongChart-specific topology checks; existing Laravel/Pest architecture tests validate framework-side behavior. No additional package is required.

### Custom implementation justification

Custom code is limited to SongChart-specific verification topology and AI workflow governance not provided by Composer/Laravel.

## Domain contract and use-case data surface

No product/domain data or route contract changes.

## Security, authorization, and data impact

No authentication, authorization, persistent schema, provider payload or sensitive-data change.

## Verification plan

- Impact lane: executable repository impact resolver.
- Focused implementation gates: topology verifier, AI protocol verifier, repository compiler, governance/static syntax.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify`.
- Packaging owner: `composer release:package`.
- Explicitly avoided duplicate/nested gates: installer/canonical shell must not separately invoke quality, PHPStan, PostgreSQL suite or frontend build around these owners.

## Tests and verification

Packaging-side: PHP syntax + all runnable static/governance verifiers. Target: locked Pint/PHPStan, architecture tests, full PostgreSQL stage closure, canonical Docker closure and provenance.

## Documentation impact

Replaces duplicated AI model instructions with thin bootstraps and rewrites current Stage 16.5.3 authority. Historical pre-roadmap Stage 16.5.3 records are preserved under `LEGACY_CONTRACT_COVERAGE`.

## Rollback

File-only rollback. No database migration or dependency rollback.

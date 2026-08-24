# Stage 16.5.3 Task Contract — Contract Coverage & Release Baseline Closure

Status: implementation contract.

## Goal

Close the remaining gap between executable contracts and implementation/delivery boundaries before Provider Operations Console work begins. Expand contract coverage to operational provider/ingestion/identity/provenance/account surfaces; bind use-case contracts to HTTP method, URI, middleware and implementation ownership; repair impact/change-surface governance; and make missing dependency lockfiles an explicit release/delivery blocker rather than guessed state.

## Non-goals

- No live provider HTTP integration.
- No provider/import mutation UI.
- No database migration or persistent data mutation.
- No authentication/authorization model change.
- No lowering of Pint, Larastan, PHPStan, SQLite, PostgreSQL or release gates.
- No fabricated `composer.lock` or `package-lock.json`.

## Acceptance criteria

- `operational-contracts.json` declares current provider, ingestion, identity, provenance and account surfaces consumed by admin/use cases.
- `use-case-contracts.json` declares exact HTTP method, URI, middleware, implementation owner, route parameters, canonical entity fields and support-surface fields.
- Verifiers reject undeclared operational fields, mismatched route method/URI, non-canonical admin middleware, missing implementation contract keys, invalid route parameters and read-only writes.
- `CatalogAdministration::show` support reads are declared explicitly.
- Admin provider/import/quarantine/conflict/user read use cases are contract-covered before Stage 16.6.
- `impact-test-map.json` contains current extension/provider/domain-contract paths and no stale extension paths.
- change-surface parsing is section-bounded and supports glob patterns.
- `ROADMAP.md` is future-looking only and does not duplicate current-stage ownership.
- delivery verification requires dependency lockfiles and current-stage governance files.
- release export refuses to run without lockfiles and a passing `composer release:verify`.
- Stage 16.5.3 does not claim target-machine release closure until those gates actually pass.

## Affected modules and boundaries

- Domain/use-case/operational contract governance.
- Static verification and impact/change-surface tooling.
- Documentation/release-delivery authority.
- Read-only controller contract ownership markers.

## Expected files

- `README.md`
- `composer.json`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/project/RELEASE_BASELINE_STATUS.md`
- `docs/project/docs/ROADMAP.md`
- `docs/project/docs/TESTING.md`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/stack/impact-test-map.json`
- `app/Support/DomainContracts/DTO/SupportDataSurface.php`
- `app/Support/DomainContracts/DTO/UseCaseContract.php`
- `app/Support/DomainContracts/UseCaseContractRegistry.php`
- `app/Http/Controllers/Search/SearchController.php`
- `app/Http/Controllers/Admin/CatalogController.php`
- `app/Http/Controllers/Admin/OperationsController.php`
- `scripts/verify-operational-contracts.php`
- `scripts/verify-use-case-contracts.php`
- `scripts/verify-type-guardrails.php`
- `scripts/verify-impact-test-map.php`
- `scripts/verify-change-surface.php`
- `scripts/verify-source-package.php`
- `scripts/verify-repository-state.php`
- `scripts/export-release-baseline.ps1`
- `tests/Architecture/ExecutableContractTypeGuardrailsTest.php`
- `tests/Architecture/ContractCoverageReleaseBaselineTest.php`
- `docs/foundation/STAGE_16_5_3_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_5_3_VALIDATION_REPORT.md`

## Allowed incidental files

- Changeset installer, manifest, README, rollback notes and backup metadata.
- Formatter-only changes to touched PHP files.
- `.changeset-backups/` created on the target machine.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/RELEASE_BASELINE_STATUS.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.3` | `composer.json` |
| Laravel | `^13.0` | `composer.json`; lockfile when present on target machine |
| PHPStan/Larastan | project dev constraints | `composer.json`; lockfile when present |
| Node/Vite | project constraints | `package.json`; lockfile when present |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | `https://laravel.com/docs/13.x/routing` | route methods, URI parameters and middleware groups | 2026-08-08 |
| Laravel | `https://laravel.com/docs/13.x/container` | typed service/registry dependencies | 2026-08-08 |
| Composer | `https://getcomposer.org/doc/01-basic-usage.md#commit-your-composer-lock-file-to-version-control` | reproducible PHP dependency lockfile policy | 2026-08-08 |
| npm | `https://docs.npmjs.com/cli/v11/commands/npm-ci` | strict lockfile-based CI installation | 2026-08-08 |
| PHP | `https://www.php.net/manual/en/function.fnmatch.php` | glob matching for declared change surfaces | 2026-08-08 |

### Native capability assessment

- Laravel route/middleware declarations remain the runtime authority; custom verification only checks repository-specific contracts against those declarations.
- Composer/npm lockfiles remain the dependency authority; SongChartWeb does not invent a secondary dependency-lock format.
- PHP `fnmatch` is sufficient for repository-local path glob enforcement; no extra package is required.

### Custom implementation justification

- Custom JSON registries are required because framework metadata does not describe SongChartWeb domain semantics, exact operational read surfaces, actor vocabulary or release-stage governance.
- Custom verifiers remain narrow: they validate repository contracts and do not replace Laravel routing, Eloquent, Composer, npm, PHPStan or database enforcement.
- No custom dependency resolver or fake lockfile generator is introduced.

## Domain contract and use-case data surface

- Actor/preconditions: repository governance plus existing guest/authenticated/admin actors; admin read contracts require active, verified, authorized users with confirmed 2FA.
- Identifier formats: existing canonical ULID/entity-type route contracts remain unchanged.
- Exact canonical fields: continue to be owned by `domain-contracts.json`.
- Exact support fields: owned by `operational-contracts.json` and referenced by use-case `support_surfaces`.
- Writes: none introduced by this stage.
- Null/unknown semantics: unchanged from existing migrations/models; undeclared fields are contract errors.
- Route/API contract: current GET search/catalog/provider/import/quarantine/conflict/user routes only.
- Relationship invariants: no new relationship semantics.

## Security, authorization, and data impact

No new privilege or write surface. The verifier strengthens the requirement that admin use cases declare the canonical middleware chain. No secrets or provider payload content are added to documentation.

## Tests and verification

- PHP syntax for all new/changed PHP files.
- `php scripts/verify-documentation.php`.
- `php scripts/verify-official-sources.php`.
- `php scripts/verify-domain-contracts.php`.
- `php scripts/verify-operational-contracts.php`.
- `php scripts/verify-use-case-contracts.php`.
- `php scripts/verify-type-guardrails.php`.
- `php scripts/verify-impact-test-map.php`.
- `php scripts/verify-repository-state.php`.
- focused architecture tests.
- Target-machine Pint, Larastan, SQLite, PostgreSQL and `composer release:verify` remain required.
- `composer delivery:verify` and release export additionally require both dependency lockfiles.

## Documentation impact

ROADMAP is converted to future-only intent, README is reduced to current operational authority, TESTING removes the obsolete npm-install fallback, and release-baseline status becomes explicit.

## Rollback

Restore all touched documentation/contract/verifier/controller files from `.changeset-backups`. No database rollback is required.

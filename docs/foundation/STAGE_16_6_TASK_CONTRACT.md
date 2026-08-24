# Stage 16.6 — Authorization Consolidation

Status: implementation candidate.

## Goal

Consolidate SongChart authorization into one machine-owned static role→capability matrix executed through Laravel Gates. Remove duplicated capability logic from `UserRole`, `User`, Gate closures and privileged services while preserving explicit business invariants and privileged audit behavior.

## Non-goals

- introducing dynamic/custom roles;
- adopting Spatie Permission;
- changing authentication or two-factor flows;
- changing role names;
- changing provider/discovery business behavior;
- changing database schema;
- weakening privileged audit;
- tenant-specific RBAC.

## Acceptance criteria

- `docs/project/security/authorization-contract.json` is the machine role→capability authority;
- `Capability` is the stable Gate-name enum;
- `AuthorizationMatrix` loads and validates the machine contract;
- `AppServiceProvider` registers all capabilities by iterating `Capability::cases()`;
- inactive users receive no capabilities regardless of role;
- `UserRole` contains no `can*` capability methods;
- `User` contains no duplicated authorization methods such as `isAdmin()` or role-capability wrappers;
- privileged role mutation authorizes through `manage-user-roles`;
- account activation mutation authorizes through `manage-user-activation`;
- SystemOperator may manage activation but not user roles;
- SuperAdmin owns every current capability;
- last-active-SuperAdmin protection remains a transactional business invariant;
- Horizon/Pulse authorization consumes the shared capability authority;
- existing verification consumer graph owns authorization consumers;
- no new authorization package is added.

## Affected modules and boundaries

Authentication/authorization governance, admin privileged operations, Gate registration, observability Gate verification and related tests/docs. No persistent schema or provider/discovery data model change.

## Changed authorities

- `authorization`
- `verification-consumer-graph`
- `executable-repository-authority`
- `ai-development-protocol`

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/security/authorization-contract.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

No dependency change. Existing PHP 8.5, Laravel 13, Pest 4, Larastan 3, PostgreSQL 18 and Node 22 authorities remain unchanged.

### Official external sources

No new dependency or external API is introduced. Laravel Gates already provide the required native authorization capability.

### Native capability assessment

Laravel Gates are sufficient for SongChart's current static role/capability model. Policies remain appropriate when authorization becomes resource-instance-specific. Spatie Permission would add unnecessary storage/runtime complexity while roles remain product-defined and static.

### Custom implementation justification

`AuthorizationMatrix` is SongChart-specific only because the product needs a machine-readable static role→capability contract shared by AI, tests and runtime. It does not replace Laravel authorization; it supplies Gate decisions.

## Expected files

- `docs/project/security/authorization-contract.json`
- `app/Enums/Capability.php`
- `app/Enums/UserRole.php`
- `app/Models/User.php`
- `app/Support/Auth/AuthorizationMatrix.php`
- `app/Providers/AppServiceProvider.php`
- `app/Support/Admin/PrivilegedUserAdministration.php`
- `app/Support/Admin/AdminInformationArchitecture.php`
- `app/Console/Commands/CreateAdminCommand.php`
- `app/Console/Commands/EnsureLocalAdminCommand.php`
- `resources/views/components/shell/frontend-header.blade.php`
- `scripts/verify-authentication-hardening.php`
- `scripts/verify-queue-infrastructure.php`
- `scripts/verify-pulse-observability.php`
- `tests/Unit/AuthorizationMatrixTest.php`
- `tests/Feature/AuthorizationGateTest.php`
- `tests/Feature/AdminDashboardTest.php`
- `tests/Feature/PrivilegedAuditTest.php`
- `tests/Architecture/AuthenticationHardeningBoundaryTest.php`
- `tests/Architecture/QueueInfrastructureBoundaryTest.php`
- `tests/Unit/PulseObservabilityContractTest.php`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/engineering/regression-ledger.json`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/generated/repository-contract-manifest.json`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `candidate-verification.json`
- `docs/foundation/STAGE_16_5_7_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_6_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_6_VALIDATION_REPORT.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/operations/pulse-observability.md`
- `docs/security/AUTHENTICATION_AND_PRIVILEGED_OPERATIONS.md`

Registered authority dependents reviewed:
- `scripts/verify-repository-contract-compiler.php`
- `scripts/resolve-repository-impact.php`
- `tests/Architecture/ExecutableRepositoryAuthorityTest.php`
- `app/Console/Commands/SongChartDoctorCommand.php`
- `scripts/compile-repository-contracts.php`
- `scripts/verify-artifact-provenance.php`
- `scripts/package-verified-source.php`
- `scripts/record-canonical-verification.php`

- `docs/project/stack/database-test-contract.json`
- `docs/project/stack/release-pipeline-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/package-schema-contracts.json`
- `docs/project/domain/model-schema-contracts.json`
- `app/Support/Engineering/RepositoryContractResolver.php`
- `scripts/snapshot-postgres-schema.php`
- `scripts/verify-package-upstream-adaptations.php`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `scripts/verify-ai-development-protocol.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`
- `routes/web.php`
- `resources/views/components/admin/sidebar.blade.php`
- `scripts/verify-laravel-alignment.php`
- `tests/Architecture/LaravelFeatureAlignmentTest.php`
- `scripts/verify-identity-conflict-review-ui.php`
- `tests/Architecture/IdentityConflictReviewUiArchitectureTest.php`
- `tests/Feature/IdentityConflictReviewUiTest.php`
## Allowed incidental files

- exact-tree generated repository contract manifest;
- formatter-only changes to listed files;
- packaging metadata outside the repository.

## Scope deviations

None planned.

## Security, authorization, and data impact

This stage strengthens authorization consistency. It does not grant a role capabilities beyond the declared matrix. Inactive users remain denied. Role and activation mutations remain audited and transactional.

## Tests and verification

Focused:
- `composer auth:verify`;
- Unit `AuthorizationMatrixTest`;
- Feature `AuthorizationGateTest`, `PrivilegedAuditTest`, `AdminDashboardTest`;
- Architecture `AuthenticationHardeningBoundaryTest`;
- queue/Pulse authorization verifiers;
- repository compiler consumer graph closure.

Candidate:
- `composer stage:verify`.

Canonical:
- `songchart verify`.

Packaging:
- authoritative full source only from the exact canonical target via `composer release:package`.

## Rollback

File-only rollback; no database/dependency rollback.

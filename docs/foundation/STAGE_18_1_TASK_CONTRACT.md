# Stage 18.1 Task Contract — Rich Entity & Multi-Provider Evidence Model

Status: current-stage task contract.

## Goal

Establish the Stage 18.1 provider ingestion model that can represent rich multi-provider evidence without letting provider payloads write canonical entities directly, provide an operator-friendly preview, and convert an accepted preview into an explicit governed import plan before any provider import run is created.

## Non-goals

- Automatic canonical mutation from provider payloads.
- Bulk all-provider import execution in this stage slice.
- Persisting preview-only pasted payloads as provider evidence.
- Declaring unsupported provider mappers as available in Admin.
- Bypassing the existing provider import orchestrator, identity resolution, validation or canonical admission boundaries.

## Acceptance criteria

- `NormalizedProviderEntity` can carry fields, identifiers, relationships, media assets, destinations, availability, classifications and metric observations.
- Provider-specific mapping is owned by typed mapper contracts and a mapper registry.
- MusicBrainz Artist and Recording payloads map through the provider-specific boundary.
- Import preview is read-only and validates normalized output before any import run or canonical admission is created.
- A valid preview can be projected into a deterministic `ProviderImportPlan` with zero direct canonical mutations.
- Plan execution recomputes preview + plan and verifies a fingerprint before creating an import run.
- Plan execution reuses `ProviderImportOrchestrator`; it does not introduce a second ingestion pipeline.
- Creating a run requires the existing `manage-providers` capability and password confirmation middleware.
- Admin wording explains the operational effect in Vietnamese and clearly distinguishes preview, plan and execution.
- Existing canonical admission, identity and validation boundaries are not weakened.

## Affected modules and boundaries

- `app/Domain/Providers/Catalog/DTO`
- `app/Domain/Providers/Normalization`
- `app/Domain/Providers/Ingestion`
- `app/Support/Providers/Normalization`
- `app/Support/Providers/Ingestion`
- `app/Actions/Providers/Ingestion`
- Admin import preview/plan HTTP surface
- `docs/project/domain/route-authority.json`

## Expected files

- Rich normalized provider DTOs and validation updates.
- Provider-specific mapper contract/registry and MusicBrainz mapper.
- Import preview DTO/builder.
- Governed import-plan DTO/builder/execution action.
- Admin preview/plan requests, controllers, views and routes.
- Unit and Feature tests for mapping, validation, preview, plan integrity and run creation.
- Current-stage governance documents and generated repository authority refreshes required by candidate verification.

## Allowed incidental files

- Pint-only formatting changes.
- `docs/project/generated/*` repository compiler outputs.
- `candidate-verification.json` only when produced by the current Stage 18.1 candidate/canonical workflow.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/providers/NORMALIZED_PROVIDER_DTOS.md`
- `docs/providers/NORMALIZATION_VALIDATION_AND_QUARANTINE.md`
- `docs/project/domain/route-authority.json`
- `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel | ^13.0 | `composer.lock` |
| PHP | ^8.5 | `composer.json` / lock/runtime authority |
| PostgreSQL | 18.x | repository runtime authority |

### Official external sources

No new package or external API capability is introduced by this stage slice. Existing MusicBrainz adapter/API authority remains owned by the previously admitted provider implementation and repository provider documentation.

### Native capability assessment

- Capability owner: SongChart provider normalization/ingestion domain.
- Native/first-party capability available: partial.
- Selected primitives: Laravel Form Request, Gates/middleware, dependency injection, Blade, queue dispatch and the existing provider import orchestrator.
- Why: the stage extends existing SongChart-owned provider semantics rather than introducing parallel infrastructure.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: provider-neutral evidence normalization, deterministic import-plan semantics and SongChart governance preview are domain-specific.
- Narrow custom boundary: provider DTOs/mappers/preview/plan projection and plan execution adapter only.
- Framework primitives reused: validation, routing, authorization middleware, controller DI, Blade and queue dispatch.

## Domain contract and use-case data surface

- Actor: authenticated active verified Admin with confirmed 2FA; execution additionally requires `manage-providers` and password confirmation.
- Input: provider slug, supported entity type, external provider identifier and bounded JSON payload; execution also submits the preview-derived plan fingerprint.
- Preview reads: request-scoped payload plus mapper/validator configuration; no canonical persistence query required.
- Execution reads: provider registry row by slug through an application action.
- Execution writes: one `provider_import_runs` row through `ProviderImportOrchestrator`; the orchestrator dispatches the existing provider fetch job.
- Canonical writes: none directly from preview or plan execution.
- Null/unknown semantics: missing, explicit null and provided values remain distinguishable through `ProviderField`.
- Output: `ProviderImportPreview` and `ProviderImportPlan` projections; successful execution redirects to the existing import-run detail surface.
- Routes: `/admin/imports/preview` and `/admin/imports/preview/execute` inside the canonical Admin imports family.
- Relationship invariants: provider relationship targets remain external identities until governed identity resolution links them.
- Contract changes: route authority extended; no canonical schema change.

## Security, authorization, and data impact

- No new permission capability.
- Preview payloads are not persisted by the preview surface.
- Execution does not trust the hidden plan: preview and plan are rebuilt, then the submitted fingerprint is compared with `hash_equals` before a run is started.
- No secrets or provider credentials are displayed or logged.
- Provider-derived values still pass normalization validation and canonical admission boundaries before canonical mutation.

## Verification plan

- Impact lane: provider normalization/ingestion, Admin Feature tests, authorization, route/repository governance.
- Focused gates: provider Unit tests, Admin preview/plan Feature test, PHPStan.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify`.
- Packaging owner: `composer release:package`.

## Tests and verification

- `tests/Unit/Providers/NormalizedProviderDtosTest.php`
- `tests/Unit/Providers/NormalizedProviderValidationTest.php`
- `tests/Unit/Providers/MusicBrainzProviderMapperTest.php`
- `tests/Unit/Providers/ProviderImportPreviewBuilderTest.php`
- `tests/Unit/Providers/ProviderImportPlanBuilderTest.php`
- `tests/Feature/Admin/ProviderImportPreviewTest.php`
- PHPStan/Larastan analysis.
- Repository/route authority verification through candidate closure.

## Documentation impact

- README current-stage pointer remains Stage 18.1.
- Development History continues to own the delivered Stage 18.1 chronology.
- This file remains the canonical current-stage task contract expected by repository-state verification.
- Slice-specific provider notes may link here but must not replace this authority.

## Rollback

Remove Stage 18.1 mapper/preview/plan/UI additions and restore the previous route authority together. Any provider import run already created by an operator remains governed operational history and must not be deleted as part of source rollback. No database schema rollback is required.

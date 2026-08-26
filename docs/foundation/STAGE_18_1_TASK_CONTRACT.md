# Stage 18.1 Task Contract — Rich Entity & Multi-Provider Evidence Model

Status: current-stage task contract.

## Goal

Establish the Stage 18.1 provider ingestion model that can represent rich multi-provider evidence without letting provider payloads write canonical entities directly, and provide an operator-friendly read-only import preview before governed import execution.

## Non-goals

- Automatic canonical mutation from provider payloads.
- Bulk all-provider import execution in this stage slice.
- Persisting preview-only pasted payloads.
- Declaring unsupported provider mappers as available in Admin.

## Acceptance criteria

- `NormalizedProviderEntity` can carry fields, identifiers, relationships, media assets, destinations, availability, classifications and metric observations.
- Provider-specific mapping is owned by typed mapper contracts and a mapper registry.
- MusicBrainz Artist and Recording payloads map through the provider-specific boundary.
- Import preview is read-only and validates normalized output before any import run or canonical admission is created.
- Admin import preview uses the existing admin middleware and route family, with operator-friendly Vietnamese wording.
- Route authority is updated before the runtime route is added.
- Existing canonical admission, identity and validation boundaries are not weakened.

## Affected modules and boundaries

- `app/Domain/Providers/Catalog/DTO`
- `app/Domain/Providers/Normalization`
- `app/Domain/Providers/Ingestion`
- `app/Support/Providers/Normalization`
- `app/Support/Providers/Ingestion`
- Admin import preview HTTP surface
- `docs/project/domain/route-authority.json`

## Expected files

- Rich normalized provider DTOs and validation updates.
- Provider-specific mapper contract/registry and MusicBrainz mapper.
- Import preview DTO/builder.
- Admin preview request/controller/view/route.
- Unit and Feature tests for mapping, validation and preview behavior.
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
- Selected primitives: Laravel Form Request, dependency injection, Blade, existing provider adapter/normalization boundaries.
- Why: the stage extends existing SongChart-owned provider semantics rather than introducing parallel infrastructure.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: provider-neutral evidence normalization, provider-specific semantic mapping and SongChart governance preview are domain-specific.
- Narrow custom boundary: provider DTOs/mappers/preview projection only.
- Framework primitives reused: validation, routing, controller DI, Blade.

## Domain contract and use-case data surface

- Actor: authenticated active verified Admin with confirmed 2FA for Admin preview access.
- Input: provider slug, supported entity type, external provider identifier and bounded JSON payload.
- Reads: request-scoped payload plus mapper/validator configuration; no canonical persistence query required for preview.
- Writes: none for preview.
- Null/unknown semantics: missing, explicit null and provided values remain distinguishable through `ProviderField`.
- Output: `ProviderImportPreview` containing validity, normalized groups, counts and validation issues.
- Route: `/admin/imports/preview` inside the canonical Admin imports family.
- Relationship invariants: provider relationship targets remain external identities until governed identity resolution links them.
- Contract changes: route authority extended; no canonical schema change.

## Security, authorization, and data impact

- No new permission capability.
- Preview payloads are not persisted by the preview surface.
- No secrets or provider credentials are displayed or logged.
- Provider-derived values still pass normalization validation and canonical admission boundaries before canonical mutation.

## Verification plan

- Impact lane: provider normalization, Admin Feature tests, route/repository governance.
- Focused gates: provider Unit tests, Admin preview Feature test, PHPStan.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify`.
- Packaging owner: `composer release:package`.

## Tests and verification

- `tests/Unit/Providers/NormalizedProviderDtosTest.php`
- `tests/Unit/Providers/NormalizedProviderValidationTest.php`
- `tests/Unit/Providers/MusicBrainzProviderMapperTest.php`
- `tests/Unit/Providers/ProviderImportPreviewBuilderTest.php`
- `tests/Feature/Admin/ProviderImportPreviewTest.php`
- PHPStan/Larastan analysis.
- Repository/route authority verification through candidate closure.

## Documentation impact

- README current-stage pointer: Stage 18.1.
- Development History records Stage 18.1 when current-stage governance is reconciled.
- This file is the canonical current-stage task contract expected by repository-state verification.
- Slice-specific provider notes may link here but must not replace this authority.

## Rollback

Remove Stage 18.1 mapper/preview/UI additions and restore the previous current-stage governance/route authority together. No database rollback is required because this slice adds no schema.

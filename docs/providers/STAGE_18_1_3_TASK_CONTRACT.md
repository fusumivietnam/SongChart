# Stage 18.1.3 Task Contract — Admin Import All-in-One Preview

Status: task contract.

## Goal

Provide one operator-friendly Admin surface that lets an authorized administrator paste a provider payload and preview how SongChart will normalize it before any import or canonical admission is started.

## Non-goals

- Starting a real import from the preview page.
- Automatic canonical admission.
- Supporting providers whose specific mapper has not been implemented yet.
- Persisting raw pasted preview payloads.
- Replacing existing provider import-run operations pages.

## Acceptance criteria

- `/admin/imports/preview` is declared in route authority before runtime route code.
- Access uses the existing authenticated/active/verified/admin/2FA middleware group.
- Preview input is validated with a Laravel Form Request and bounded to MusicBrainz Artist/Recording for this slice.
- Invalid JSON or a non-object JSON payload returns normal validation feedback, not a server error.
- A valid MusicBrainz payload produces a read-only preview through the existing provider mapper registry and normalization validator.
- The UI explains in Vietnamese that preview does not write data.
- Preview shows evidence counts plus normalized fields, identifiers and relationships in operator language.
- No provider import run or canonical data is persisted by preview.
- Unit tests remain container-independent; HTTP/controller behavior is covered by Feature tests.

## Affected modules and boundaries

- Provider normalization mapper registry.
- Provider ingestion preview read model.
- Admin imports UI.
- HTTP route authority.
- Laravel request-validation/controller transport boundary.
- No schema, queue, scheduler, provider credential or canonical mutation boundary changes.

## Expected files

- `docs/providers/STAGE_18_1_3_TASK_CONTRACT.md`
- `docs/project/domain/route-authority.json`
- `app/Http/Requests/Admin/ProviderImportPreviewRequest.php`
- `app/Http/Controllers/Admin/ProviderImportPreviewController.php`
- `resources/views/admin/operations/import-preview.blade.php`
- `resources/views/admin/operations/imports.blade.php`
- `routes/web.php`
- `tests/Feature/Admin/ProviderImportPreviewTest.php`

## Allowed incidental files

- `docs/project/generated/repository-contract-manifest.json` when refreshed by the repository compiler/candidate workflow.
- Candidate verification/context artifacts owned by the existing delivery workflow.
- Pint-only formatting changes inside files listed above.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/domain/route-authority.json`
- `docs/project/domain/application-data-boundary.json`
- `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`
- `docs/providers/NORMALIZED_PROVIDER_DTOS.md`
- `docs/providers/NORMALIZATION_VALIDATION_AND_QUARANTINE.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.5` | `PROJECT_AUTHORITY.md`, Composer platform resolution |
| Laravel | `^13.0` | `PROJECT_AUTHORITY.md`, `composer.lock` |
| Pest/PHPStan | repository locked | `composer.lock` |

### Official external sources

No new external package or provider API capability is introduced by this slice. The implementation reuses Laravel Form Request, routing, Blade and validation primitives already admitted by repository authority. Provider payload semantics are handled by the MusicBrainz mapper introduced in Stage 18.1.2; this slice does not broaden that provider contract.

### Native capability assessment

- Capability owner: Laravel HTTP routing/request validation + SongChart provider normalization.
- Native/first-party capability available: yes.
- Selected official API or primitive: Laravel routes, Form Request validation, Blade views and existing service-container injection.
- Why it satisfies the requirement: transport validation and rendering need no parallel request framework or custom admin transport layer.

### Custom implementation justification

- Custom code required: yes, narrowly.
- Missing official behavior: Laravel does not understand SongChart provider payload semantics or rich evidence projection.
- Narrow custom boundary: `ProviderImportPreviewController` adapts validated HTTP input to the existing SongChart `ProviderPayload` and `ProviderImportPreviewBuilder`.
- Framework primitives reused: route group, Form Request, controller injection, validation redirect/error bag, Blade components.
- Non-goals: custom validation framework, custom routing infrastructure, persistence mutation.

## Domain contract and use-case data surface

- Actor and preconditions: authenticated, active, verified administrator with Admin access and confirmed 2FA.
- Input types and identifier formats: `provider_slug=musicbrainz`; `entity_type=artist|recording`; provider external ID string; JSON object payload up to 1 MiB.
- Exact entity fields read: none from canonical persistence.
- Exact entity fields written: none.
- Null/unknown semantics: inherited from `ProviderField` and provider mapper; missing, explicit null and provided remain distinct.
- Output DTO/presentation contract: `ProviderImportPreview` with validity, counts, validation issues and normalized evidence envelope.
- Route/API contract: GET and POST `/admin/imports/preview` inside the existing Admin middleware family.
- Relationship invariants: provider relationship targets remain external provider identities until identity resolution; preview does not link canonical entities.
- Contract changes required: yes, route authority only.
- `domain-contracts.json` entries affected: none.

## Security, authorization, and data impact

- Uses existing Admin authentication, active-user, email-verification, `access-admin` and 2FA middleware.
- Preview is read-only and does not require password confirmation because no privileged mutation is performed.
- Pasted payload is request-scoped and is not persisted by this surface.
- UI explicitly warns operators not to include API keys/secrets.
- No raw payload logging is introduced.
- No canonical/provider-policy boundary is weakened.

## Verification plan

- Impact lane: Admin HTTP + provider normalization + route authority.
- Focused implementation gates: Provider mapper/preview unit tests, `tests/Feature/Admin/ProviderImportPreviewTest.php`, PHPStan, relevant route/controller architecture checks.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify`.
- Packaging owner: `composer release:package`.
- Explicitly avoided duplicate/nested gates: no separate full PostgreSQL/build closure before candidate unless a focused failure requires it.

## Tests and verification

- Feature test: authorized Admin can open preview workspace.
- Feature test: valid MusicBrainz Recording preview displays normalized identity/relationship evidence and creates no import run.
- Feature test: non-object JSON returns validation feedback.
- Existing normalization mapper/DTO/validator tests remain required focused coverage.
- PHPStan/Larastan required.
- PostgreSQL-backed Feature test runs in authoritative Docker test lane.
- Checks not performed at contract authoring time: stage closure and canonical closure.

## Documentation impact

- Update route authority for `/admin/imports/preview`.
- Task contract records the new Admin use case.
- No new ADR: no new architectural pattern or dependency is introduced.
- Generated repository authority may require refresh after route-authority change.

## Rollback

Remove the preview GET/POST routes, request/controller/view and imports-page entry point; revert the route-authority entry and task files. No schema/data rollback is required because this slice has no persistence writes.

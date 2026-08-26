# Stage 18.1.3 — Admin Import All-in-One Preview

Status: implementation contract

## Goal

Provide one operator-friendly Admin surface that lets an authorized administrator paste or inspect a provider payload and preview how SongChart will normalize it before any import or canonical admission is started.

## Scope

- Add an Admin import-preview route inside the existing `/admin/imports` family.
- Support MusicBrainz Artist and Recording payload preview first.
- Reuse `ProviderSpecificMapperRegistry`, `ProviderImportPreviewBuilder` and normalization validation.
- Show normalized fields, identifiers, relationships and rich evidence counts in Vietnamese operator language.
- Keep the preview read-only: no provider import run, provider entity, assertion, identity match, destination or canonical mutation is created.

## Non-goals

- Starting a real import from the preview page.
- Automatic canonical admission.
- Supporting providers whose specific mapper has not been implemented yet.
- Persisting raw pasted preview payloads.
- Replacing existing provider import-run operations pages.

## Acceptance criteria

1. `/admin/imports/preview` is declared in route authority before runtime route code.
2. Access uses the existing authenticated/active/verified/admin/2FA middleware group.
3. Preview input is validated with a Laravel Form Request and bounded to the supported provider/entity combinations.
4. Invalid JSON or unsupported input returns normal validation feedback, not a server error.
5. A valid MusicBrainz payload produces a read-only preview through the existing provider mapper registry and validator.
6. The page clearly states that preview does not write data.
7. Unit tests remain container-independent; HTTP/controller behavior is covered by Feature tests.
8. PHPStan, focused Pest tests and route/domain authority verification remain green.

## Affected modules

- Provider normalization
- Provider ingestion preview
- Admin imports UX
- HTTP route authority

## Data/provider/policy impact

No schema change. No provider credential change. No canonical write policy change. Preview payloads are request-scoped and are not persisted by this surface.

## Verification

- `tests/Feature/Admin/ProviderImportPreviewTest.php`
- existing provider normalization unit tests
- `vendor/bin/phpstan analyse`
- route/repository authority verification through candidate closure

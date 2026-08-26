# Stage 18.1 Corrective — Provider Setup + Search-first Import + Node 24 LTS

Status: implementation candidate; validation pending exact-tree gates.

## Goal

Make provider operations usable from Admin without requiring routine `.env`, provider IDs or raw JSON, while preserving the existing governed ingestion/admission boundaries. Align Docker and GitHub application build runtime on Node 24 LTS.

## User experience

- Admin → Nguồn dữ liệu exposes configuration for currently supported runtime providers.
- MusicBrainz operator identity can be configured in Admin.
- YouTube API key can be entered in Admin and is stored encrypted; the stored secret is never rendered back to HTML or audit payloads.
- Admin → Nhập dữ liệu is search-first: operators can start with an artist/group name or recording title.
- Raw provider ID + JSON remains available only as an advanced diagnostic path.
- Lyrics-fragment intent is visible but fails honestly with an actionable message until a governed lyrics provider exists.

## Runtime/data boundaries

- Provider runtime settings are DB overrides with environment configuration as fallback.
- Runtime provider overrides are applied at provider workbench/search boundaries and provider-import queue execution.
- Secrets are encrypted with Laravel Crypt before persistence inside the provider configuration envelope.
- Audit records contain configured secret keys only, never secret values.
- Provider configuration writes are transaction-owned by `ProviderConfigurationService` and require Admin `manage-providers` plus password confirmation.
- Search/select surfaces are read-only. Selecting a result fetches a first-party provider payload and rebuilds the existing `ProviderImportPreview` + `ProviderImportPlan`; it does not create an import run.
- Import execution continues to use the Stage 18.1.4 fingerprint check and governed `ProviderImportOrchestrator`.
- No provider payload writes canonical entities directly.

## Provider capability scope

- MusicBrainz: search-first Artist and Recording discovery; operator User-Agent configuration.
- YouTube: encrypted Data API key configuration used by the existing destination workbench.
- Spotify, Apple Music, SoundCloud and Wikidata: registry remains visible, but Admin does not claim ingestion configuration support until their adapters are implemented and verified.

## Node runtime alignment

- `docker/verify/Dockerfile` is the shared dev/verification image authority and uses Node 24 LTS.
- GitHub Actions quality/frontend lanes use Node 24.
- `docs/project/stack/stack-manifest.json` declares `node_runtime_major = 24`.
- `scripts/verify-stack-baseline.php` fails on Node 22 drift.
- No frontend dependency upgrade is required solely for this runtime alignment.

## HTTP surfaces

- `POST /admin/providers/{provider}/configuration` — privileged provider configuration write.
- `POST /admin/imports/search` — read-only provider discovery.
- `POST /admin/imports/select` — read-only selected-result detail fetch + governed preview/plan build.
- Existing `POST /admin/imports/preview` remains the advanced raw-payload preview.
- Existing `POST /admin/imports/preview/execute` remains the only new-import execution boundary for this workbench.

## Security

- Provider API secrets must use password inputs and are never pre-filled.
- Plaintext secrets must not appear in `provider_operation_audits` or privileged audit state.
- Configuration writes retain idempotency and privileged audit.
- All external provider calls remain server-side through registered provider adapters/workbenches.

## Verification

Focused verification:

- `tests/Feature/Admin/ProviderImportPreviewTest.php`
- `tests/Feature/Admin/ProviderConfigurationTest.php`
- provider normalization/import-plan unit tests
- `composer admin-operations-ux:verify`
- `composer ci:configuration`
- `composer stack:verify`
- PHPStan/Larastan

Closure remains owned by `./songchart candidate`, followed by canonical verification on the exact committed tree. No PASS is claimed by this document until those gates are actually run.

## Rollback

Remove the Admin configuration/search routes, runtime override services and search-first UI together; restore Node runtime authority/Docker/CI together if runtime rollback is required. Do not restore plaintext provider secrets or direct provider-to-canonical writes.

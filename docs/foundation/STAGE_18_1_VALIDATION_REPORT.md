# Stage 18.1 Validation Report — Rich Entity & Multi-Provider Evidence Model

Status: current-stage validation record; exact-tree verification pending after the latest provider UX/runtime changes.

## Scope currently implemented

Stage 18.1 now includes:

- rich normalized provider evidence envelope;
- provider-specific MusicBrainz Artist/Recording mapping;
- governed preview → import-plan → confirmed provider import execution;
- search-first Admin import discovery for artist/group names and recording titles;
- explicit unsupported-state messaging for lyrics-fragment search until a governed lyrics provider exists;
- Admin provider runtime configuration for MusicBrainz operator identity and encrypted YouTube API key;
- DB-backed runtime provider overrides with environment fallback;
- Node 24 LTS alignment across Docker development/verification and GitHub application build lanes;
- CI feedback-loop optimization and Admin import discoverability corrections.

## Prior verification evidence

The user previously reported:

- `[SongChart Docker stage] PASSED.`
- `[SongChart candidate] PASSED.`

Those PASS results belong to the exact source trees before the latest search-first import, provider configuration and Node 24 changes. They must not be reused to claim this new tree passes.

## Verification required for the current tree

Run after pulling the latest branch:

- `tests/Feature/Admin/ProviderImportPreviewTest.php`;
- `tests/Feature/Admin/ProviderConfigurationTest.php`;
- existing provider import-plan/normalization focused tests;
- `composer admin-operations-ux:verify`;
- `composer ci:configuration`;
- `composer stack:verify`;
- PHPStan/Larastan;
- `./songchart candidate` on the exact committed tree;
- `./songchart verify` only after candidate PASS.

Generated repository authority must be refreshed and committed when candidate requests it.

## Security/data assessment

- No new canonical data write path is introduced.
- Search/select surfaces remain read-only and feed the existing governed preview/plan pipeline.
- Provider configuration writes are privileged, password-confirmed, transactional, idempotent and audited.
- YouTube API keys are encrypted before persistence and are not rendered back into Admin HTML or audit state.
- Provider import workers apply the same runtime provider configuration used by Admin search/workbench requests.
- Environment values remain fallback configuration rather than the routine operator configuration surface.
- Unsupported provider capabilities are shown as unavailable rather than simulated.

## Closure rule

Do not mark Stage 18.1 canonical or package-ready until candidate and canonical verification pass on the exact current tree after all generated artifacts are committed.

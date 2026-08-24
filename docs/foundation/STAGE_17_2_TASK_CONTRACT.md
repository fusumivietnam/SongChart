# Stage 17.2 — Artist Import Vertical Slice

Status: implementation candidate.

## Goal

Prove the first live SongChart data path end-to-end with MusicBrainz Artist data: live search/selection in the local control center, queued lookup import, immutable provider evidence, provider-neutral normalization, validation/quarantine, exact identity resolution, canonical Artist mutation, admin inspection, and a canonical public Artist URL.

## Non-goals

- no Release/Release Group/Recording/Work ingestion;
- no bulk MusicBrainz crawl;
- no public-page calls to MusicBrainz;
- no fuzzy automatic entity merge;
- no search-engine package or external queue product;
- no production-facing provider workbench;
- no commercial-use approval claim.

## Acceptance criteria

- `/development/status` can perform an explicit live MusicBrainz Artist search when configured;
- a selected MBID queues `artist-lookup` through `ProviderImportOrchestrator` rather than mutating canonical tables from the controller;
- the existing payload ledger stores raw MusicBrainz evidence before normalization/mutation;
- normalized Artist data includes MBID, name, sort name, disambiguation, country, lifespan evidence, ended state and Artist type;
- exact provider/external identifiers drive identity resolution; ambiguity continues to the existing conflict-review boundary;
- canonical Artist mutation remains provider-neutral and uses collision-safe slugs for same-name artists;
- `/artist/{slug}` resolves the canonical Artist without live provider access;
- Docker development queue worker listens to SongChart custom import/normalization queues;
- Docker app/queue receive MusicBrainz variables from the project `.env` while Docker DB/Redis authority remains unchanged;
- Docker dev setup ensures the provider registry rows exist without overwriting an operator-enabled provider; live import still requires MusicBrainz to be enabled in the registry;
- no controller performs direct canonical DB writes.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/docs/ROADMAP.md`
- `app/Support/Providers/Ingestion/ProviderImportOrchestrator.php`
- `app/Jobs/Providers/Ingestion/*`
- `app/Support/Providers/Mutation/*`
- `compose.dev.yml`

### Installed versions

No package addition. Runtime remains Laravel 13 / PHP 8.5 authority from `composer.lock` and Docker definitions, PostgreSQL 18.4, Redis 7.4 and Caddy 2.11.3.

### Official external sources

- MusicBrainz API: https://musicbrainz.org/doc/MusicBrainz_API
- MusicBrainz Search API: https://musicbrainz.org/doc/MusicBrainz_API/Search
- MusicBrainz rate limiting: https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
- MusicBrainz data license: https://musicbrainz.org/doc/About/Data_License

Current policy evidence: ordinary read access does not require an API key but requires a meaningful User-Agent; the web service is rate-limited and commercial use requires the applicable MetaBrainz commercial terms/review.

### Native capability assessment

- Laravel HTTP client remains the provider transport primitive.
- Laravel Redis cache locks remain the one-request-per-second serialization primitive.
- Laravel queue workers and Redis remain the import orchestration primitive.
- Eloquent/PostgreSQL transactions remain the ledger/identity/canonical mutation primitive.
- No third-party MusicBrainz SDK is needed because the existing provider adapter contract isolates WS/2 schema from the domain.

### Custom implementation justification

SongChart needs a narrow provider-neutral workbench/orchestration bridge because Laravel does not define MusicBrainz entity semantics, provider identity rules, immutable import evidence, or canonical Artist mutation. Custom code is limited to that domain/application boundary and reuses existing framework/provider infrastructure.

## Security, authorization, and data impact

The live workbench exists only in local/testing routes. It does not expose credentials or raw provider payloads publicly. POST import remains CSRF-protected. Canonical public pages read only persisted SongChart data. MusicBrainz enablement and User-Agent configuration fail closed.

## Expected files

- MusicBrainz Artist workbench/application service and development import controller;
- local control-center Blade updates;
- provider normalization/canonical Artist refinements;
- Docker queue/config wiring;
- focused Feature tests;
- current-stage README/history/roadmap/governance evidence.

## Allowed incidental files

Formatting-only changes to directly touched files and generated candidate metadata required by repository verification.

## Scope deviations

Any migration, new provider entity type, package installation, production provider UI, or fuzzy identity behavior requires explicit contract expansion before implementation.

## Tests and verification

- PHP syntax sweep;
- `tests/Feature/Development/MusicBrainzArtistWorkbenchTest.php`;
- existing provider ingestion/normalization/mutation tests;
- Docker local-development verifier;
- test-taxonomy verifier;
- official-source verifier;
- repository-state/documentation/repository-contract verifiers;
- iterative `stage-verify.bat` when needed;
- final closure through `verify-songchart.bat`, which already includes stage verification.

## Rollback

Restore Stage 17.1.3 application/docs/compose files. No database migration rollback is required.

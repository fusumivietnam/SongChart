# Stage 17.3.2 — Admin Provider Import Workbench & Public Catalog Indexes

Status: implementation candidate.

## Goal

Move the proven MusicBrainz Artist search/import workflow into the governed Admin provider surface and make the public navigation paths `/artists`, `/releases`, and `/collections` resolve to honest canonical catalog indexes instead of 404 responses.

## Non-goals

- no Release/Recording/Work provider ingestion;
- no automatic collection creation;
- no YouTube integration;
- no new schema migration;
- no public exposure of private collections;
- no replacement of Laravel routing, pagination, validation, authorization, or the existing provider ingestion pipeline.

## Acceptance criteria

- `/admin/providers/{provider}` shows a MusicBrainz Artist search/import workbench only for the MusicBrainz provider;
- live search reuses `MusicBrainzArtistWorkbench` and the registered provider adapter instead of adding a second API client;
- selected Artist import uses the existing immutable-ledger/provider-orchestrator pipeline;
- Admin import requires the canonical admin middleware chain plus `can:manage-providers` and recent password confirmation;
- `/artists` lists canonical Artists already present in SongChart and links to existing `/artist/{slug}` detail pages;
- `/releases` is routable and renders canonical releases when present, with an explicit empty state while Release ingestion remains future scope;
- `/collections` lists only `visibility=public` collections and never exposes private collections;
- frontend header/mobile navigation no longer points to 404 paths;
- no migration or package change is required.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `app/Support/Providers/Catalog/MusicBrainzArtistWorkbench.php`
- `app/Support/Admin/ProviderOperationsConsole.php`

### Installed versions

No dependency changes. Runtime remains PHP 8.5, Laravel 13, PostgreSQL 18.4, Redis 7.4, and the existing Docker development/canonical verification profile.

### Official external sources

- Laravel 13 routing: https://laravel.com/docs/13.x/routing
- Laravel 13 pagination: https://laravel.com/docs/13.x/pagination
- Laravel 13 authorization: https://laravel.com/docs/13.x/authorization
- MusicBrainz API search: https://musicbrainz.org/doc/MusicBrainz_API/Search
- MusicBrainz Artist search fields: https://musicbrainz.org/doc/MusicBrainz_API/Search/ArtistSearch

### Native capability assessment

Laravel named routes, implicit web middleware, controller dependency injection, Eloquent pagination, authorization middleware, request validation, and existing SongChart provider orchestration fully cover this stage. No package or custom routing/pagination framework is needed.

### Custom implementation justification

SongChart needs a small public catalog read model because public collection visibility and entity-specific index presentation are product rules that should not live in Blade or controllers. The Admin workbench custom code only composes the existing MusicBrainz adapter/workbench and provider import orchestrator; it does not introduce a parallel provider client or ingestion path.

## Security, authorization, and data impact

Public catalog indexes are read-only. Collections are filtered to `visibility=public`. MusicBrainz search is visible only inside the existing authenticated/active/verified/admin/2FA provider detail surface. Import mutation additionally requires `can:manage-providers` and `password.confirm`. Provider evidence and canonical mutation remain owned by the existing ingestion pipeline.

## Tests and verification

- `tests/Feature/PublicCatalog/PublicCatalogBrowseTest.php` covers Artist index, Release empty/data states, and private Collection non-disclosure;
- `tests/Feature/Admin/MusicBrainzProviderWorkbenchTest.php` covers live Admin search and governed import dispatch;
- use-case, official-source, documentation, repository-state, taxonomy, repository-contract, and candidate-contract verification;
- final target closure via `verify-songchart.bat`.

## Rollback

Restore Stage 17.3.1 application/governance files. No database rollback is required because Stage 17.3.2 adds no migration.

# Stage 17.4 — MusicBrainz Release Group + Release Vertical Slice

Status: implementation candidate.

## Goal

Extend the proven MusicBrainz provider pipeline from Artist to distinct Release Group and Release canonical identities, preserving edition semantics, provenance, request governance and public/admin inspection without opening Recording ingestion early.

## Non-goals

- no Recording/ISRC or Work ingestion;
- no automatic full-discography crawler;
- no YouTube provider/API admission;
- no Cover Art Archive binary download or local artwork storage;
- no label canonical entity expansion;
- no provider-specific bypass around the shared request gate.

## Acceptance criteria

- `EntityType` and the executable domain contract include a distinct `release_group` entity;
- a forward-only migration creates `release_groups` and adds nullable `releases.release_group_id` without modifying frozen historical migrations;
- MusicBrainz adapter supports Artist, Release Group and Release lookup/search through the same provider request gate;
- Release Group and Release payloads enter the immutable provider import ledger and existing normalization/validation/identity/canonical mutation pipeline;
- MusicBrainz MBIDs are attached as explicit external identifiers for both new surfaces;
- Release Group primary/secondary types and disambiguation are retained;
- Release edition status/date/country/barcode/catalog-number/packaging/media track-count evidence is retained;
- only complete dates populate PostgreSQL date columns; partial dates remain assertions;
- a Release links to its canonical Release Group when that group identity has already been imported;
- Admin → Providers → MusicBrainz can search/import Release Groups and Releases;
- `/releases` renders imported canonical Releases and `/release/{slug}` resolves a release detail shortcut;
- MusicBrainz-reported front artwork is retained as a Cover Art Archive HTTPS reference only; no binary is fetched;
- existing Artist import/retry/rate-governance behavior remains intact.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/providers/STAGE_17_4_MUSICBRAINZ_RELEASE_CONTRACT.md`
- `app/Support/Providers/Catalog/MusicBrainzProviderCatalogAdapter.php`
- `app/Support/Providers/Mutation/EloquentCanonicalMutationAction.php`

### Installed versions

No package changes. Runtime baseline remains PHP 8.5+, Laravel 13, PostgreSQL 18.x and Redis 7.4 in Docker development/canonical profiles.

### Official external sources

- MusicBrainz API: https://musicbrainz.org/doc/MusicBrainz_API
- MusicBrainz Search API: https://musicbrainz.org/doc/MusicBrainz_API/Search
- MusicBrainz Release Group: https://musicbrainz.org/doc/Release_Group
- MusicBrainz Release: https://musicbrainz.org/doc/Release
- Cover Art Archive API: https://musicbrainz.org/doc/Cover_Art_Archive/API

### Native capability assessment

Existing Laravel HTTP client, queue jobs, validation, Eloquent models/migrations, Redis-backed provider request gate, exact-identity resolver and canonical mutation pipeline already cover the required mechanics. A forward-only Laravel migration is the native way to add Release Group storage and the Release foreign key. No package is justified.

### Custom implementation justification

MusicBrainz Release Group versus Release semantics are provider/domain concepts that Laravel does not model. SongChart therefore adds the smallest canonical distinction and provider normalization required to preserve those official semantics while reusing all existing framework/runtime primitives.

## Security, authorization, and data impact

Admin import remains under the canonical Admin middleware chain plus `can:manage-providers` and recent password confirmation. No provider credentials are added. Raw provider data stays in the existing ledger; canonical schema adds one Release Group table and one nullable Release foreign key.

## Tests and verification

- adapter Feature coverage for Release Group/Release lookup and normalization;
- canonical mutation Feature coverage for Release Group creation, Release linking and partial-date safety;
- Admin workbench Feature coverage for Release Group/Release search and governed import;
- domain/use-case/schema ownership/provider catalog/provider mutation/import orchestration/taxonomy/type/repository gates;
- target migration/PostgreSQL/Pest/PHPStan/Pint/frontend/canonical closure via `verify-songchart.bat`.

## Rollback

Apply the migration down only when explicitly rolling back the Stage 17.4 schema, then restore Stage 17.3.3 application/governance files. Normal release rollback should use the forward migration lifecycle expected by SongChart environments.

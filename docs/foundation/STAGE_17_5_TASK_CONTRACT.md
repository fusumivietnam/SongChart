# Stage 17.5 — MusicBrainz Recording + ISRC Vertical Slice

Status: implementation candidate.

## Goal

Extend the MusicBrainz canonical pipeline to Recording identity so SongChart can model an actual sound recording, retain ISRC evidence and ordered multi-Artist credits, expose public Recording browse/detail surfaces, and establish the identity foundation required before YouTube matching.

## Non-goals

- no Work/ISWC relationship ingestion;
- no group-member relationship expansion;
- no automatic discography crawler;
- no YouTube API/provider admission;
- no guessed release-track positions;
- no auto-creation of missing Artists/Releases from relationship payloads.

## Acceptance criteria

- MusicBrainz adapter advertises Recording lookup and supports Recording search/lookup through the shared provider request gate;
- lookup requests `artist-credits+isrcs+releases` and stores the raw payload in the existing immutable import ledger;
- Recording normalization retains title, duration, disambiguation, first-release evidence, video flag evidence and ISRCs;
- MBID and every supplied ISRC become external identifiers;
- ordered Artist Credit relationships are normalized and already-known canonical Artists are linked in `artist_recording`;
- Release appearances link only when matching canonical Release identities already exist;
- Recording slug generation is collision-safe;
- Admin → Providers → MusicBrainz can search/import Recording;
- `/recordings` lists canonical Recordings and `/recording/{slug}` resolves detail;
- existing Artist/Release Group/Release import and global rate governance remain intact.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/providers/STAGE_17_5_MUSICBRAINZ_RECORDING_CONTRACT.md`
- `app/Support/Providers/Catalog/MusicBrainzProviderCatalogAdapter.php`
- `app/Support/Providers/Mutation/EloquentCanonicalMutationAction.php`

### Installed versions

No package or runtime-version changes. Baseline remains PHP 8.5+, Laravel 13, PostgreSQL 18.x and Redis 7.4 in Docker development/canonical profiles.

### Official external sources

- MusicBrainz API: https://musicbrainz.org/doc/MusicBrainz_API
- MusicBrainz Recording Search: https://musicbrainz.org/doc/MusicBrainz_API/Search/RecordingSearch
- MusicBrainz database schema / Recording semantics: https://musicbrainz.org/doc/MusicBrainz_Database/Schema
- MusicBrainz ISRC: https://musicbrainz.org/doc/ISRC

### Native capability assessment

Existing Laravel HTTP client, Redis-backed provider request gate, queues, validation, Eloquent, existing Recording/artist_recording schema, provider import ledger, exact identity resolver and canonical mutation pipeline already provide the mechanics. No package or new migration is required.

### Custom implementation justification

MusicBrainz Recording, Artist Credit and ISRC semantics are provider/domain concepts not supplied by Laravel. SongChart adds only the provider normalization/workbench and minimal canonical-link logic necessary to preserve them.

## Security, authorization, and data impact

Admin Recording import uses the existing Admin authentication/active/verified/access-admin/2FA/manage-providers/password-confirm chain. No credentials or secret storage changes. No schema migration. Provider raw payload and provenance contracts remain unchanged.

## Tests and verification

- MusicBrainz Recording adapter lookup/normalization coverage;
- canonical Recording mutation + ISRC + artist-credit linking coverage;
- Admin Recording search/import workbench coverage;
- public `/recordings` and `/recording/{slug}` route/read-model coverage;
- provider catalog/import/mutation, use-case, domain, taxonomy, official-source, repository and canonical Docker verification.

## Rollback

Restore Stage 17.4 application/governance files. No database rollback is required because Stage 17.5 adds no migration.

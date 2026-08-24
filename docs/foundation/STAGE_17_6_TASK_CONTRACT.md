# Stage 17.6 — Essential MusicBrainz Relationships

Status: implementation candidate.

## Goal

Complete the minimum MusicBrainz graph required before YouTube integration: group membership, Recording→Work identity, full Artist Credit display evidence, aliases and selected URL relationships, while keeping canonical identity provider-neutral.

## Non-goals

- no YouTube API/provider;
- no long-tail MusicBrainz relationship types;
- no fuzzy/heuristic auto-merge;
- no bulk recursive discography crawler;
- no event/place/series/instrument graph;
- no arbitrary URL crawling.

## Acceptance criteria

- Artist lookup requests `artist-rels+url-rels+aliases`;
- `member of band` becomes canonical `member_of`/`has_member` with temporal metadata;
- relation targets can be minimally materialized from stable MBID + display metadata without bypassing provenance;
- Recording lookup requests `work-rels` and links/materializes Work with MBID/ISWC evidence;
- ordered Recording Artist Credit persists credited name and join phrase;
- Work lookup/search is admitted through the same rate gate/import ledger/canonical pipeline;
- `/works` is a public canonical index and entity detail pages surface canonical relationships;
- existing Artist/Release/Recording behavior remains intact.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/providers/STAGE_17_6_MUSICBRAINZ_RELATIONSHIPS_CONTRACT.md`

### Installed versions

No package/runtime version changes. Baseline remains PHP 8.5+, Laravel 13, PostgreSQL 18.x and Redis 7.4 in Docker profiles.

### Official external sources

- MusicBrainz API relationships/includes: https://musicbrainz.org/doc/MusicBrainz_API
- MusicBrainz Artist Credits: https://musicbrainz.org/doc/Artist_Credits
- MusicBrainz Artist Credit style: https://musicbrainz.org/doc/Style/Artist_Credits
- MusicBrainz Works: https://musicbrainz.org/doc/How_to_Use_Works

### Native capability assessment

Existing Laravel HTTP/queue/cache/Eloquent primitives, provider rate gate, raw ledger, exact identity resolver, canonical mutation pipeline and provenance tables remain sufficient. One additive migration is required for relationship metadata and Artist Credit display details.

### Custom implementation justification

MusicBrainz relationship direction, MBID/ISWC semantics and Artist Credit join phrases are music-domain/provider semantics not supplied by Laravel. Custom code is limited to normalization and canonical mapping.

## Security, authorization, and data impact

Admin Work import reuses the existing authenticated/active/verified/access-admin/2FA/manage-providers/password-confirm chain. No secrets change. Migration only adds nullable metadata/credit columns.

## Tests and verification

- artist membership/aliases/URL adapter normalization;
- Recording→Work normalization and Work lookup;
- canonical member/Work materialization with provenance;
- Artist Credit join phrase persistence;
- Work Admin import and `/works` public browse;
- existing provider/domain/use-case/migration/repository/canonical verification.

## Rollback

Restore Stage 17.5 application/governance files and roll back `2026_08_17_000200_add_essential_relationship_metadata.php` if Stage 17.6 schema was applied.

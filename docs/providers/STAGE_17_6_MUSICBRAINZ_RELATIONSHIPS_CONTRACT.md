# Stage 17.6 MusicBrainz Essential Relationships Contract

Status: implementation candidate.

## Scope

MusicBrainz remains the metadata/identity authority. Stage 17.6 admits only relationships needed for SongChart's near-term visitor experience:

- Artist ↔ Artist `member of band`, normalized as `member_of` / `has_member` according to MusicBrainz direction;
- Recording → Work, normalized as `recording_of`;
- ordered Recording Artist Credit details including credited name and join phrase;
- Artist aliases as provider assertions;
- selected Artist URL relationships as external URL identifiers;
- Work MBID/ISWC identity and direct Work lookup/search for operator inspection.

Long-tail instruments, events, places, series, labels and arbitrary URL graph expansion remain out of scope.

## Canonical materialization policy

A related Artist or Work may be minimally materialized only when MusicBrainz supplies a stable target MBID and enough target display metadata in a relationship payload. Such records remain `candidate`, retain MusicBrainz identity/provenance, and can be enriched by a later direct import. No fuzzy matching is used.

## Relationship metadata

Temporal/credit evidence is stored on canonical relationship metadata rather than flattened into Artist columns. Recording Artist Credit keeps ordered position, credited name and join phrase.

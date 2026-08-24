# Stage 17.4 MusicBrainz Release Contract

Status: provider implementation authority for the Stage 17.4 candidate.

## Purpose

Extend the proven MusicBrainz Artist ingestion boundary to the minimum album/release graph required before Recording ingestion.

## Entity semantics

- `release_group` is the logical album/single/EP identity. It is canonicalized separately from editions.
- `release` is a concrete edition/issue with its own date, country, barcode, packaging, label/catalog-number and media evidence.
- a MusicBrainz Release may reference exactly one Release Group; SongChart stores the group MBID as normalized evidence and links `releases.release_group_id` when the canonical group already exists.
- partial MusicBrainz dates (`YYYY` / `YYYY-MM`) remain metadata assertions and are not coerced into PostgreSQL `date` columns. Only complete `YYYY-MM-DD` dates populate canonical date fields.

## MusicBrainz operations

| SongChart operation | MusicBrainz resource | Purpose |
|---|---|---|
| `release-group.search` | `/ws/2/release-group` | Admin candidate discovery |
| `release-group.lookup` | `/ws/2/release-group/{mbid}` | Raw ledger import |
| `release.search` | `/ws/2/release` | Admin edition discovery |
| `release.lookup` | `/ws/2/release/{mbid}` | Raw ledger import |

Release lookup requests `release-groups+artist-credits+labels+media` so the raw ledger retains edition context without opening Recording ingestion early.

## Normalization

Release Group normalized evidence:

- title;
- primary type;
- secondary types;
- first release date;
- disambiguation;
- MusicBrainz Release Group MBID;
- artist-credit relationships when corresponding canonical Artists already exist.

Release normalized evidence:

- title;
- status;
- primary Release Group type;
- Release Group MBID;
- release date and country;
- barcode;
- first label catalog number;
- packaging;
- aggregate media track count;
- MusicBrainz Release MBID;
- Cover Art Archive front-image reference when MusicBrainz reports front artwork;
- artist-credit and Release Group relationships when canonical targets exist.

## Request governance

All operations resolve policy through `ProviderRatePolicyRegistry` and request slots through `ProviderRequestGate`. Stage 17.4 does not add a separate Release limiter or bypass the provider-global MusicBrainz gate.

## Non-goals

- Recording/track canonical ingestion;
- ISRC ingestion;
- Work relationships;
- Cover Art Archive HTTP fetching/caching;
- label canonical entities;
- automatic bulk crawling of an artist's full discography.

# MusicBrainz

Status: Stage 17.1 first live provider contract; live traffic is disabled by default pending local configuration/policy review.

## Use

- artists, releases, release groups, recordings, works, labels, areas and relationships;
- MBIDs as external identifiers;
- ISRC/ISWC/barcode/disc ID cross-reference where returned.

## Rules

- Maximum one request per second per application/IP under the public service guidance.
- Send a meaningful User-Agent with application version and contact.
- Import asynchronously and cache normalized results.
- Do not call MusicBrainz during public page rendering.
- Preserve MusicBrainz entity type distinctions.
- Do not treat community data as editor-verified fact automatically.
- Store MBID in `provider_entities`.
- Maintain merge/redirect handling because MusicBrainz entities can be merged.

Official references:
- https://musicbrainz.org/doc/MusicBrainz_API
- https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting


## Stage 17.1 implementation

- Adapter: `App\Support\Providers\Catalog\MusicBrainzProviderCatalogAdapter`.
- Enabled capabilities: artist lookup and artist search only.
- Shared-cache request serialization enforces the public one-request-per-second ceiling.
- `MUSICBRAINZ_ENABLED=false` is the default fail-closed state.
- A placeholder User-Agent is rejected before any network request.
- Artist payloads normalize into provider-neutral artist DTO fields plus `musicbrainz_artist` MBID.

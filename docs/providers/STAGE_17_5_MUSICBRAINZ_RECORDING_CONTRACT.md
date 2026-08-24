# Stage 17.5 — MusicBrainz Recording Contract

## Purpose

Admit MusicBrainz Recording as the canonical sound-recording identity used by SongChart before any YouTube media-destination matching.

## Provider semantics

- MusicBrainz Recording represents a unique mix/edit, not a composition/work and not a track-position row.
- MusicBrainz MBID is the provider primary identity.
- ISRC values are external identifiers for the recording and may be multiple.
- Artist Credit is ordered and can include multiple Artists; SongChart preserves known canonical Artist links in `artist_recording` and provider relationships.
- Release appearances are retained as relationships only when the target canonical Release identity is already known; Stage 17.5 does not invent release-track positions from Recording lookup data.
- duration comes from MusicBrainz `length` in milliseconds.
- Work relationships and richer join-phrase presentation remain Stage 17.6 scope.

## API operations

- search: `/ws/2/recording?query=...&fmt=json`
- lookup: `/ws/2/recording/{mbid}?inc=artist-credits+isrcs+releases&fmt=json`
- ISRC lookup remains compatible with later enrichment but is not a separate Admin lane in Stage 17.5.

All requests use the provider-neutral `ProviderRequestGate` introduced in Stage 17.3.3.

## Canonical mapping

| MusicBrainz | SongChart |
|---|---|
| `id` | external identifiers `provider:musicbrainz` + `musicbrainz_recording` |
| `title` | `recordings.title` |
| `length` | `recordings.duration_ms` |
| `isrcs[]` | external identifiers namespace `isrc` |
| `artist-credit[]` | `performed_by` relationships + `artist_recording` for already-known Artists |
| `releases[]` | related Release relationship when already known |
| `disambiguation` | provider metadata assertion |

## Guardrails

- no automatic Artist creation from Recording payloads;
- no fuzzy identity merge;
- no YouTube search or media attachment in this stage;
- no guessed track number/disc number;
- duplicate title slugs must remain collision-safe;
- raw payload remains immutable evidence.

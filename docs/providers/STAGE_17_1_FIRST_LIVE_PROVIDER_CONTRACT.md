# Stage 17.1 — First Live Provider Contract

Status: implemented candidate; canonical closure requires Docker verification.

## Provider

MusicBrainz is the first live catalog provider. Stage 17.1 intentionally enables only artist lookup and artist search at the adapter boundary. Release, recording, work and relationship expansion belong to later vertical slices.

## Contract

- API root: `https://musicbrainz.org/ws/2/`.
- Read-only catalog requests do not require an API key.
- Every request must send a meaningful SongChart User-Agent with real contact information.
- Public API traffic passes through the provider-neutral `ProviderRequestGate`; MusicBrainz currently uses the `minimum_interval` strategy with a 1100 ms safety interval shared by HTTP/Admin and queue workers.
- Live traffic is disabled by default with `MUSICBRAINZ_ENABLED=false`.
- Public-page rendering never calls MusicBrainz; requests flow through the asynchronous provider ingestion boundary.
- Raw provider JSON remains immutable evidence; normalized data is provider-neutral and carries the MBID as `musicbrainz_artist`.
- Stage 17.1 supports only `ArtistLookup` and `Search`; unsupported entity types fail closed.

## Configuration

```env
MUSICBRAINZ_ENABLED=false
MUSICBRAINZ_BASE_URL=https://musicbrainz.org/ws/2
MUSICBRAINZ_USER_AGENT="SongChartWeb/0.1 (real-contact@example.com)"
MUSICBRAINZ_CONNECT_TIMEOUT_SECONDS=5
MUSICBRAINZ_TIMEOUT_SECONDS=15
MUSICBRAINZ_RATE_STRATEGY=minimum_interval
MUSICBRAINZ_MINIMUM_INTERVAL_MS=1100
MUSICBRAINZ_DEFAULT_COOLDOWN_SECONDS=2
MUSICBRAINZ_MAXIMUM_COOLDOWN_SECONDS=900
MUSICBRAINZ_RATE_LOCK_WAIT_SECONDS=10
```

Enable the provider only after replacing the contact placeholder and reviewing the current MusicBrainz service/commercial-use requirements.

See `docs/providers/PROVIDER_RATE_POLICY.md` for shared request-gate semantics, cooldown handling, and future-provider extension rules.

## Development control center

`/development/status` now shows MusicBrainz adapter/configuration/database readiness, import-run counts, item count and open identity conflicts, plus direct links to admin provider/import/identity surfaces.

## Official sources reviewed

- https://musicbrainz.org/doc/MusicBrainz_API
- https://musicbrainz.org/doc/MusicBrainz_API/Search
- https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
- https://musicbrainz.org/doc/About/Data_License

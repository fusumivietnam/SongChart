# SongChart Provider Integration Rules v1

Research snapshot: 2026-08-02.

This pack defines which providers SongChart may integrate, why they exist in the architecture, and the rules AI coding agents must follow.

## Recommended baseline

### Phase 1 — implement first

1. MusicBrainz — canonical music identity and relationships.
2. Cover Art Archive — release artwork tied to MusicBrainz entities.
3. Wikidata — biographies, external identities, locations and knowledge graph enrichment.
4. YouTube — official video discovery, outbound links and compliant embeds.
5. ListenBrainz — optional discovery and listening-history integration.
6. AcoustID — optional recording identification from fingerprints supplied by authorized users.
7. Manual/editorial provider — first-party corrections and curated links.

### Phase 2 — adapters behind feature flags

8. Apple Music — catalog/deep links; MusicKit only after developer credentials and legal review.
9. SoundCloud — track discovery, embeds and user integration after approved credentials.
10. Last.fm — tags, popularity signals and user listening integration; commercial use requires contact.
11. Deezer — catalog/deep links only after confirmed app access and current terms.
12. Spotify — optional account/catalog adapter only after production access is approved; never a core dependency.

### Phase 3 — commercial or specialist

13. Discogs — physical-release and label enrichment after access/licensing verification.
14. Songkick — live events only under paid partnership/licence.
15. setlist.fm — concert setlists after commercial-use and cache/attribution review.
16. Lyrics providers — Musixmatch or another licensed provider only; no scraping.
17. Genius — annotations/outbound links only if an approved API use case exists; never scrape lyrics.

## Architectural position

- PostgreSQL canonical entities remain the source of truth.
- Provider payloads are assertions, not canonical truth.
- Every provider is optional and protected by a feature flag and kill switch.
- Public pages must render without live provider calls.
- Provider IDs are alternate identities, never internal primary keys.
- Provider media is never downloaded, transcoded or re-hosted.

## Public destination rendering

Before exposing any outbound provider URL, apply the Stage 08 destination gate: available status, approved compliance state, HTTPS, provider host allowlist, visible market/check metadata and safe external-link attributes. Unknown or stale assertions stay disabled.

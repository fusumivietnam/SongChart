# Stage 17.7 YouTube Provider Foundation + Video Destination Contract

## Role boundary

- MusicBrainz remains canonical music identity/metadata authority.
- YouTube is a media destination provider attached primarily to canonical Recording.
- SongChart does not download, proxy, store, or rehost YouTube audio/video.
- Candidate discovery is not canonical mutation. Human approval is required before a destination becomes actionable.

## Official API surface

- `search.list` is used only to discover video candidates from canonical Recording title + ordered Artist Credit.
- `videos.list` with `snippet,contentDetails,status` revalidates selected IDs and supplies channel/title/duration/privacy/embeddable evidence.
- `search.list` quota is operation-specific and is guarded independently from MusicBrainz minimum-interval policy.
- API key stays server-side; it must never be rendered into HTML or provider destination URLs.

Official sources reviewed 2026-08-18:
- https://developers.google.com/youtube/v3/docs/search/list
- https://developers.google.com/youtube/v3/docs/videos/list
- https://developers.google.com/youtube/v3/determine_quota_cost

## Candidate scoring

Stage 17.7 computes bounded evidence from canonical title, Artist Credit, duration delta, video title and channel title. Any `official/topic/vevo` textual signal is heuristic only and never establishes channel ownership. Score states (`recommended`, `review`, `weak`) help operators sort candidates; operator approval remains authoritative.

## Persistence

Approved destinations are stored in `provider_destinations` with provider resource ID, canonical entity reference, public URL, channel metadata, duration, embeddability, privacy state, score/evidence and verification timestamps. Public pages expose only approved destinations and apply a 30-day freshness gate.

## Deferred to 17.8

IFrame playback, player event handling, destination refresh jobs, channel authority enrichment, quota dashboards and artist-level featured-video composition are deferred.

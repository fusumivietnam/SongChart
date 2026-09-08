# YouTube View Count Chart Contract

Status: Stage 22 production metric contract.

## Metric

- provider: `youtube`
- provider capability: `media.statistics`
- metric key: `youtube_video_view_count`
- unit: `views`
- semantics version: `youtube-view-count-2026-08-24`
- source method: YouTube Data API v3 `videos.list`
- source part: `statistics,status`
- eligible source: approved, public YouTube destination attached to a canonical SongChart Recording

## Semantics boundary

YouTube changed the meaning of video `viewCount` effective 2026-08-24 for all video formats. SongChart therefore treats that date as a metric-semantics version boundary. Observations with another semantics version must not be ranked in the same snapshot.

The Stage 22 chart ranks the current public view count of each approved YouTube destination. It does not claim cross-provider stream equivalence, unique listeners, popularity, or SongChart user engagement.

## Provenance

Every observation carries:

- immutable observation fingerprint;
- canonical Recording id;
- YouTube video id;
- metric key and unit;
- metric semantics version;
- observed/fetched timestamp;
- numeric value;
- provider source reference.

The persisted snapshot retains the full observation set plus an input fingerprint and calculation version.

## Failure policy

- disabled/misconfigured YouTube: fail closed; do not publish a new snapshot;
- destination not approved/public: exclude it;
- missing/non-numeric/out-of-bound `viewCount`: fail the refresh rather than coerce data;
- no eligible destinations: create no snapshot;
- no persisted snapshot: public chart route returns 404;
- previous immutable snapshots are never rewritten to hide later provider failures.

## Runtime activation

Automatic refresh is opt-in through `SONGCHART_YOUTUBE_VIEW_CHART_SCHEDULE_ENABLED=true` and runs hourly. It should only be enabled after YouTube policy/quota review, API credentials, and approved canonical destinations are configured.

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

The Stage 22 chart ranks the current public view count of approved YouTube destinations. For a canonical Recording with multiple eligible approved public YouTube destinations, the chart score is the **sum of the eligible destination `viewCount` observations in that refresh input set**. This aggregation is explicit chart-definition behavior; it does not imply that the destinations represent unique listeners or non-overlapping audiences.

The metric does not claim cross-provider stream equivalence, unique listeners, popularity, or SongChart user engagement.

## Observation history

Every accepted metric observation is persisted append-only in `chart_metric_observations` before chart calculation. The observation id is immutable and idempotent: replaying the same observation id with identical evidence is a no-op, while reusing the same id for different evidence fails closed.

Persisted history retains:

- canonical Recording id;
- provider and provider item id;
- metric key and unit;
- metric semantics version;
- numeric value and value kind;
- observed/fetched timestamps;
- provider source reference.

Historical observations are not rewritten when a later refresh changes the provider value.

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

The persisted snapshot retains the full observation set plus an input fingerprint and calculation version. `docs/project/domain/chart-definitions.json` owns chart-level input, aggregation, semantic and freshness constraints.

## Failure policy

- disabled/misconfigured YouTube: fail closed; do not publish a new snapshot;
- destination not approved/public: exclude it;
- missing/non-numeric/out-of-bound `viewCount`: fail the refresh rather than coerce data;
- no eligible destinations: create no snapshot;
- observation-id collision with different evidence: fail closed;
- no persisted snapshot: public chart route returns 404;
- previous immutable observations and snapshots are never rewritten to hide later provider failures.

## Runtime activation

Automatic refresh is opt-in through `SONGCHART_YOUTUBE_VIEW_CHART_SCHEDULE_ENABLED=true` and runs hourly. It should only be enabled after YouTube policy/quota review, API credentials, and approved canonical destinations are configured.

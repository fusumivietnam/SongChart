# Stage 22 — Blocker Report

Status: Stage 22 implementation is complete; acceptance remains verification/runtime-evidence gated.

## Implemented

- Critical intersection audit contains no unknown production edge.
- Golden provider/canonical cases cover replay, multi-provider agreement, conflict, ambiguous identity and remaster/version semantics.
- Canonical admission emits `CanonicalEntityChanged`; Discovery reprojection and Recording chart refresh are bounded consumers.
- Chart observations require canonical Recording identity, provider item identity, metric/unit, semantics version, timestamps and source provenance.
- YouTube `videos.list(part=statistics,status)` is the first production metric source, restricted to approved public YouTube destinations.
- YouTube view-count semantics are versioned at `youtube-view-count-2026-08-24`; incompatible semantics cannot share a snapshot.
- Chart calculation is deterministic, calculation-versioned and input-fingerprinted.
- `chart_snapshots` persists self-contained immutable/idempotent snapshots.
- `/charts/{chart}` reads only persisted snapshots and returns 404 when none exists.
- `charts:refresh-youtube-views` provides explicit refresh; optional hourly scheduling is disabled by default until policy/quota/credential readiness is accepted.
- Roadmap naming is reconciled: Stage 22 is Production Vertical Closure; Stage 23 owns richer public chart UX.

## Remaining blocker 1 — Exact-head Auto Closure

Stage acceptance requires the exact final PR head to pass the governed PREPARE, QUALITY, impacted database/browser/frontend lanes and canonical CLOSE. A successful older head is not sufficient.

### Remediation

Run/allow `SongChart Auto Closure` on the exact final head. If any job fails, fix the failure owner from its log and re-run on the new exact head. Do not manually mark Stage 22 accepted.

## Remaining blocker 2 — Live production activation evidence

The repository can now fetch a real YouTube metric, but this environment does not expose a production YouTube API credential or guarantee an approved public `ProviderDestination`. Test HTTP fixtures prove request/normalization/persistence behavior but are not a live provider observation.

### Remediation

1. Complete YouTube API policy/quota review.
2. Configure an API key through the governed provider credential path.
3. Enable the YouTube provider.
4. Approve at least one public YouTube destination mapped to a canonical Recording.
5. Run `php artisan charts:refresh-youtube-views` once.
6. Verify a new `chart_snapshots` row retains the canonical Recording id, YouTube video id, `youtube_video_view_count`, semantics version, timestamp, source reference and input fingerprint.
7. Verify `/charts/youtube-video-views` renders the same canonical Recording URL.
8. Only then enable `SONGCHART_YOUTUBE_VIEW_CHART_SCHEDULE_ENABLED=true` if hourly quota usage is accepted.

## Metric caveat

YouTube view count is a YouTube media statistic, not a cross-provider stream count, unique-listener count, or SongChart popularity score. SongChart must not combine it with another provider unless a future metric contract proves compatible unit and semantics.

## Closure rule

Stage 22 may be marked accepted only after exact-head Auto Closure passes. Production deployment of the chart additionally requires the live activation smoke above; absent credentials, the implementation remains fail-closed and publishes no fabricated chart.

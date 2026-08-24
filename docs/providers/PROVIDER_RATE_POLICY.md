# Provider Rate Policy & Global Request Gate

Status: Stage 17.3.3 provider-runtime authority.

## Purpose

All outbound provider traffic that can originate from HTTP/Admin, queue workers, scheduled refreshes, or CLI tooling must share one provider-scoped request-governance boundary. Provider adapters own endpoint semantics; they do not own distributed throttling state.

## Runtime model

```text
HTTP/Admin search ----\
Queue import ----------+--> ProviderRatePolicyRegistry --> ProviderRequestGate --> external provider
Scheduled refresh ----/
                                              |
                                              +--> shared cache/Redis state
                                                   provider-rate:{provider}:*
```

The first implemented strategy is `minimum_interval`. `none` exists as the fail-open/no-throttle strategy for providers whose official contract does not require a local request interval. Additional strategies are added only when a real provider requires them.

## MusicBrainz policy

MusicBrainz remains the first live proof of the generic gate:

- strategy: `minimum_interval`;
- default minimum interval: 1100 ms;
- all Admin search and queue import requests share the same provider-scoped gate;
- HTTP 429/503 records a shared cooldown;
- `Retry-After` is respected when present, bounded by the provider policy maximum cooldown;
- a request arriving during cooldown fails before network dispatch with a retryable `ProviderRequestException` and the remaining delay;
- the Admin provider detail surface exposes strategy, minimum interval, gate state, and cooldown remaining time.

MusicBrainz official API rules require client applications to make no more than one call per second and document HTTP 503 as the decline response when limits are exceeded.

## Future providers

The contract is intentionally operation-aware (`provider + operation`) so later providers can differentiate calls such as `video.search`, `video.lookup`, or metadata refreshes. Do not model YouTube as a one-request-per-second provider: YouTube Data API governance is quota-based and will require its own strategy when that provider is admitted.

## Configuration

```env
MUSICBRAINZ_RATE_STRATEGY=minimum_interval
MUSICBRAINZ_MINIMUM_INTERVAL_MS=1100
MUSICBRAINZ_DEFAULT_COOLDOWN_SECONDS=2
MUSICBRAINZ_MAXIMUM_COOLDOWN_SECONDS=900
MUSICBRAINZ_RATE_LOCK_WAIT_SECONDS=10
```

Docker dev forwards these variables to both `app` and `queue`, so the shared Redis-backed cache/lock state governs both execution paths.

## Official sources

- https://musicbrainz.org/doc/MusicBrainz_API
- https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
- https://developers.google.com/youtube/v3/determine_quota_cost
- https://developers.google.com/youtube/v3/getting-started

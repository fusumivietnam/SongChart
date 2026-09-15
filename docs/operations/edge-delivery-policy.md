# Edge Delivery Policy

Status: Stage 25.0A provider-neutral policy baseline.

## Purpose

SongChart may place a CDN/proxy or other edge layer in front of Caddy only when Stage 24 operational evidence demonstrates a measurable delivery problem. This document describes the accepted policy boundary; it does not activate Cloudflare, Workers, Hyperdrive, a load balancer, or any other external infrastructure.

The machine authority is `docs/project/domain/route-authority.json` under `edge_delivery`.

## Supported fallback

Direct DNS-to-Caddy remains supported. Caddy keeps TLS/static-edge ownership for the application runtime. An external edge profile may sit in front of Caddy, but it must implement the repository policy rather than becoming a second source of route, authentication, cache, or application truth.

## Shared-cache classes

- Immutable Vite assets under `/build/*` may use long-lived shared/browser caching because content hashes provide invalidation.
- `robots.txt` and `sitemap.xml` are anonymous metadata and may use bounded shared caching.
- Anonymous public catalog/entity reads may use a short edge TTL with explicit canonical-content/deployment invalidation.
- Persisted chart reads use a shorter TTL and must invalidate on chart snapshot promotion.
- `/search*` bypasses shared cache until traffic and correctness evidence justify an accepted query/cache-key policy.
- `/account*`, `/admin*`, and `/development*` always bypass shared cache.

Only `GET` and `HEAD` can be shared-cache eligible. Any other method bypasses.

## Mandatory bypass

Shared caching must be bypassed when an Authorization header or governed session/XSRF cookie is present, or when the origin response is private/no-store or emits `Set-Cookie`. External edge configuration must preserve these fail-closed rules.

These bypass rules intentionally prioritize privacy/correctness over cache hit ratio. A low cache hit ratio caused by authenticated/session traffic is not sufficient evidence to weaken the boundary.

## Evidence gate

Stage 25 may investigate edge delivery only when the Stage 24 scale scorecard is `investigate` and at least one accepted runtime/cache signal is warning. The current signals are `pulse_slow_events_15m` and `cache_hit_ratio`.

Infrastructure adoption additionally requires measured public request volume, public p95 latency, and origin request rate. The policy requires an expected improvement of at least 20% in public p95 latency or 30% in origin request rate before an external edge is promoted from evaluation to an adopted production profile. These are investigation/adoption thresholds, not promises that a provider will achieve them.

`insufficient_evidence`, `within_baseline`, and `stabilize_before_scaling` explicitly defer external-edge adoption. In particular, known critical failures must be stabilized before capacity or CDN work is used as a workaround.

## Provider evaluation

Cloudflare-compatible CDN/proxy and Workers capabilities may be evaluated against this policy. Workers remain evaluation-only and must not duplicate Laravel domain/business logic. Provider-specific cache configuration, purge APIs, pricing, privacy, retention, quotas, and exit/fallback behavior must be documented before adoption.

## Verification

`composer route-authority:verify` validates the safety boundary, including protected-route exclusion, bypass headers/cookies, invalidation declarations, Stage 24 metric references, provider neutrality, Caddy fallback, and disabled automatic infrastructure mutation.

`tests/Feature/EdgeDeliveryPolicyTest.php` covers the same product-critical invariants in the application test suite.

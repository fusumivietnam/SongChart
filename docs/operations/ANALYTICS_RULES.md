# Analytics Rules

## Analytics layers

1. Product analytics: user journeys, funnels, retention, discovery and provider outbound success.
2. Web analytics: traffic source, landing pages, devices and public-page performance.
3. Operational analytics: errors, latency, queues, provider health and email delivery.
4. Business reporting: catalog quality, editorial throughput and moderation workload.

Do not merge all four into one dashboard.

## Recommended tools

- PostHog: product events, funnels, feature flags, experiments and optional session replay.
- Sentry: technical errors, performance and affected sessions.
- Pulse: Laravel queues, requests, slow jobs and application activity.
- Business dashboards: generated from SongChart's own database.

## Event rules

Events describe completed facts:
- `search_submitted`
- `search_result_selected`
- `entity_viewed`
- `provider_link_opened`
- `embed_started`
- `collection_saved`
- `registration_completed`

Every event has:
- owner;
- purpose;
- trigger;
- allowed properties;
- prohibited properties;
- retention;
- dashboard/funnel using it.

Never send raw search text by default. Prefer normalized categories or explicit privacy review.

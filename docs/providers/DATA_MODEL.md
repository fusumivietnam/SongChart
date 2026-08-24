# Provider Data Model

Recommended tables:

- `providers`
- `provider_capabilities`
- `provider_policy_reviews`
- `provider_credentials`
- `user_provider_accounts`
- `provider_entities`
- `provider_assertions`
- `provider_availability`
- `provider_attributions`
- `provider_sync_runs`
- `provider_request_metrics`
- `provider_webhook_receipts`
- `provider_deletion_requests`

## Important constraints

`provider_entities` unique:
- provider_id
- entity_type
- external_id
- market nullable

`provider_availability` includes:
- canonical_entity_id
- provider_entity_id
- market
- url
- availability_status
- checked_at
- expires_at

User tokens are stored separately from public identities and encrypted at rest.

Never put `spotify_id`, `youtube_id`, `apple_music_id`, etc. directly on canonical tables.

## UI projection requirement

Provider availability projected to public UI must retain `availability_status`, `market`, `checked_at`, `expires_at`, attribution and compliance state. A URL alone is never sufficient to create an actionable destination.

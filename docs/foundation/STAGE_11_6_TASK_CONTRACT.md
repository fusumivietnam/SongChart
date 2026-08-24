# Stage 11.6 Task Contract — Async and Provider Infrastructure

## Goal

Establish Laravel-native queue and scheduler boundaries for provider operational work without adding live provider adapters or allowing public requests to depend on provider availability.

## Acceptance criteria

- Provider adapters are discovered through one registry owned by the application container.
- Provider health checks run in a queued, retry-safe and idempotent job.
- Dispatch creates auditable `provider_sync_runs` records.
- Disabled providers are never dispatched.
- Recent queued/running health checks are deduplicated unless an operator explicitly forces dispatch.
- Laravel Scheduler dispatches provider health checks without overlapping across nodes.
- Exceptions are reported and summarized without storing raw provider payloads or secrets.
- Public pages remain free from live provider calls.
- Tests cover registry behavior, dispatch filtering, deduplication and successful job completion.

## Non-goals

- Implementing MusicBrainz, YouTube, Spotify or other live adapters.
- Adding provider credentials or user token storage.
- Changing provider compliance status from operational health checks.
- Adding Horizon, Pulse, Telescope or a new queue package.
- Building provider imports, webhooks or metadata merge workflows.
- Changing public provider destination behavior.

## Data impact

No migration is required. Stage 11.6 reuses `provider_sync_runs` as the operational audit log.

## Operational impact

A queue worker must process the configured provider queue. Laravel Scheduler must run once per minute in production.

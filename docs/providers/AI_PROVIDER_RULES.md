# AI Provider Coding Rules

## Source of truth

Before implementing or changing a provider, the agent must:

1. read this pack and the provider-specific file;
2. inspect the current official API reference, changelog and terms;
3. record `policy_reviewed_at`;
4. use the official OpenAPI specification when available;
5. never infer endpoint paths, fields, scopes or retention rules.

## Hard prohibitions

- No scraping provider websites.
- No unofficial download endpoints.
- No extraction of audio/video files.
- No bypassing geographic, subscription or player restrictions.
- No live provider request in a public page render.
- No storing access tokens unencrypted.
- No provider-specific fields leaking into canonical domain models.
- No silent fallback that misattributes one provider's data to another.
- No production enablement without registry status `approved`.
- No use of an unofficial SDK when a simple HTTP adapter is safer, unless reviewed.

## Adapter contract

Each adapter must expose only declared capabilities:

- search;
- lookup;
- resolveExternalUrl;
- listAvailability;
- embed;
- importUserLibrary;
- importListeningHistory;
- writePlaylist;
- healthCheck;
- revoke.

Unsupported capabilities return a typed `CapabilityUnavailable`, not null or fabricated data.

## Request rules

- server-side HTTP client;
- explicit connect/read timeout;
- provider-specific rate limiter;
- retry only retry-safe requests;
- honor `Retry-After`;
- exponential backoff with jitter;
- circuit breaker;
- request correlation ID;
- redact secrets from logs;
- cache according to provider terms;
- collect quota headers where exposed.

## Data rules

All normalized fields retain:
- provider;
- external ID;
- retrieved timestamp;
- market;
- source URL;
- raw fingerprint/version;
- confidence or match method;
- policy version where relevant.

Raw payload storage is opt-in per provider and time-bounded.

## UI rules

- Show only supported capabilities.
- Name the provider on outbound actions.
- Explain external navigation.
- Never imply universal availability.
- Never show a provider logo as proof of canonical correctness.
- Attribution must be rendered from provider policy configuration.

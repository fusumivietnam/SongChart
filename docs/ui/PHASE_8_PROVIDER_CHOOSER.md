# Stage 08 — Provider Chooser

Status: implemented in SongChart 0.1.0-dev, Stage 08.

## Purpose

Provide a safe outbound-routing experience without implying that SongChart hosts or streams media.

## Required provider payload

Each destination includes:

- `key`, `name`;
- `capability`, `capability_label`;
- `status`, `status_label`, `availability_reason`;
- `url`, `market`;
- `checked_at`, `expires_at`;
- `attribution`;
- `compliance_state`;
- `action_label`.

## Actionability gate

A destination is actionable only when all are true:

1. status is `available`;
2. compliance state is `approved`;
3. URL uses HTTPS;
4. host is allowlisted for the provider key;
5. the URL exists in the normalized payload.

Unknown, stale, pending-policy and malformed destinations remain visible but disabled. UI must explain why.

## UX rules

- State labels are textual and never color-only.
- Market and last checked date are visible.
- External actions open in a new tab with `noopener noreferrer external`.
- The chooser states that SongChart is not the playback surface.
- Provider-specific login, advertising, territory and terms may apply.
- Availability never determines canonical identity.

## Architecture

`ProviderDestinationPolicy` owns destination safety checks. Blade must not independently parse hosts or invent provider state. Catalog adapters normalize provider assertions before rendering.

## Regression rules

- Never render an actionable link for stale or unknown destinations.
- Never trust a provider URL solely because status says available.
- Never add a new provider domain without updating the allowlist, provider policy docs and tests.
- Assertions should target provider semantic attributes and href presence/absence, not decorative CSS.

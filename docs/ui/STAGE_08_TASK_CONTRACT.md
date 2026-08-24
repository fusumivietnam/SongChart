# Stage 08 Task Contract — Provider Chooser

## Goal

Turn provider routing into a governed, safe, explicit destination chooser.

## Non-goals

- no provider API calls;
- no playback or autoplay;
- no account connection;
- no fabricated availability;
- no production provider repository.

## Acceptance criteria

- available, unknown and stale states are explicit;
- only fresh, approved HTTPS destinations on provider allowlists are actionable;
- market, capability, checked date and attribution are visible;
- external links use a new tab and safe rel values;
- provider status remains separate from canonical identity;
- UI Preview and Feature tests cover the pattern.

## Data and policy impact

Provider payloads are assertions. They must include compliance state and checking metadata. Missing values disable the destination rather than being guessed.

# Spotify

Status: P2 blocked for core dependency; experimental only after approved access.

## Potential use

- account linking;
- user profile/library/playlist functions that remain available;
- catalog links and metadata within approved scope;
- playback control for eligible users/devices.

## Current risk

Spotify changed Development Mode materially in February 2026 and continues to apply quota restrictions. Development Mode is positioned for non-commercial learning, experimentation and personal projects.

## Rules

- Do not base canonical import, search, authentication or primary playback on Spotify.
- Require an approved production access path before public integration.
- Use Authorization Code with PKCE for user-specific browser flows.
- Request minimum scopes.
- Treat endpoint/field availability as versioned and unstable.
- Read the current changelog before every implementation.
- Handle quota limits separately from rate limits.
- Do not cache or display Spotify content beyond current policy.
- Preserve a complete no-Spotify fallback.

Official references:
- https://developer.spotify.com/documentation/web-api
- https://developer.spotify.com/documentation/web-api/concepts/quota-modes
- https://developer.spotify.com/documentation/web-api/concepts/rate-limits
- https://developer.spotify.com/documentation/web-api/tutorials/february-2026-migration-guide

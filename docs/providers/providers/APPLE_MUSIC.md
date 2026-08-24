# Apple Music / MusicKit

Status: P1 gated.

## Potential use

- catalog search and lookup;
- market-aware deep links;
- Apple Music Feed if approved;
- subscriber-authorized library, recommendations and recent history;
- MusicKit playback where terms permit.

## Rules

- Require Apple developer setup, media identifier/private key and developer tokens.
- User-specific features require user authorization and Music User Token.
- Availability is storefront/market-specific.
- Never make playback a prerequisite for SongChart's core value.
- Never proxy or store streamed audio.
- Playback must remain user initiated.
- Separate catalog metadata from personalized user data.
- Review MusicKit monetization, attribution and preview usage before enabling.
- Build direct-link functionality before full playback integration.

Official references:
- https://developer.apple.com/musickit/
- https://developer.apple.com/documentation/applemusicapi/
- https://developer.apple.com/musickit/web/

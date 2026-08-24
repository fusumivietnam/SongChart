# Provider Capability Model

## Capabilities

- `catalog.search`
- `catalog.lookup`
- `identity.cross_reference`
- `metadata.enrich`
- `artwork.read`
- `availability.read`
- `embed.render`
- `playback.control`
- `user.profile.read`
- `user.library.read`
- `user.history.read`
- `user.collection.write`
- `recommendations.read`
- `events.read`
- `setlists.read`
- `lyrics.display`
- `fingerprint.lookup`

## Capability declaration

A provider must declare per capability:

- status: unsupported / experimental / approved / suspended;
- authentication type;
- required scopes;
- market dependence;
- cache TTL;
- persistence permission;
- attribution requirement;
- user consent requirement;
- deletion requirement;
- fallback behavior.

UI and jobs must query capability status rather than compare provider names.

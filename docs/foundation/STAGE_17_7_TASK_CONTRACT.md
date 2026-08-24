# Stage 17.7 Task Contract

## Scope

Deliver YouTube Provider Foundation + Video Destination on the proven canonical Recording model, while isolating canonical verification lifecycle from the long-lived development Compose project.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/providers/STAGE_17_7_YOUTUBE_DESTINATION_CONTRACT.md`
- `compose.dev.yml`
- `compose.verify.yml`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PostgreSQL 18 is the canonical database verification authority.
- Docker Compose v2 owns local dev/verification orchestration.

### Official external sources

Reviewed 2026-08-18:

- YouTube Data API `search.list`: https://developers.google.com/youtube/v3/docs/search/list
- YouTube Data API `videos.list`: https://developers.google.com/youtube/v3/docs/videos/list
- YouTube Data API quota calculator: https://developers.google.com/youtube/v3/determine_quota_cost

### Native capability assessment

- Use Laravel HTTP client for server-side YouTube Data API calls.
- Use Laravel Cache for bounded operation-specific quota counters.
- Use Eloquent and a forward-only migration for approved provider destination persistence.
- Use Docker Compose `-p` project naming to isolate verification lifecycle; do not create a second Docker orchestration implementation.

### Custom implementation justification

- YouTube is not a canonical music metadata authority, so it must not enter the MusicBrainz catalog mutation pipeline. A small provider-neutral destination discovery contract separates candidate discovery/verification from canonical identity mutation.
- Candidate scoring is deterministic evidence only. Text suggesting `official/topic/vevo` is explicitly heuristic and cannot establish channel ownership; operator approval remains authoritative.
- `provider_destinations` is required because approved/fresh playable destinations have lifecycle/evidence fields that do not belong in canonical `external_identifiers`.

## Tests and verification

- Verify canonical Compose commands always use project name `songchart-verify` and do not implicitly target the dev project.
- Verify YouTube is disabled without explicit configuration/API key.
- Verify `search.list` discovery is followed by `videos.list` verification.
- Verify approval writes one Recording-owned provider destination and public detail exposes only approved/fresh HTTPS destinations.
- Run migration lifecycle, schema ownership, model/schema, use-case, architecture, PHPStan, PostgreSQL/Pest and canonical verification.

## Acceptance criteria

- Canonical verification teardown cannot remove long-lived development containers through Compose project-name collision.
- YouTube remains disabled by default and secrets stay server-side.
- Canonical Recording is the destination owner.
- Human approval persists a provider-neutral destination; no media bytes are stored or rehosted.
- Public provider chooser only opens approved/fresh allowlisted HTTPS destinations.

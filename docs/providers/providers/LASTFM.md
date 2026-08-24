# Last.fm

Status: P1/P2 depending commercial approval.

## Potential use

- tags;
- listener/popularity signals;
- similar artists;
- public/user-authorized listening history;
- scrobbling if SongChart ever becomes a player.

## Rules

- Obtain an API account.
- Contact Last.fm before commercial or research/academic use as requested in official docs.
- Do not hit the API on page load.
- Cache responsibly and avoid several calls per second.
- User write methods require authentication.
- Popularity and tags are behavioral/community signals, not canonical facts.
- Keep MBID and original Last.fm identity when mapping.

Official reference:
- https://www.last.fm/api

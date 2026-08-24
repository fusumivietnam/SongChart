# Stage 17.8.1 Validation Report

## Candidate
Stage 17.8.1 — Public Taxonomy & Detail Runtime Corrective

## Corrected failures
- Canonical plural entity detail pages returned 404 for deterministic local/testing fixtures.
- `result-row` assumed every fixture payload contained `url`.
- Public artist taxonomy did not distinguish solo/person artists from group/orchestra/choir Artist subtypes.

## Resulting public policy
- `/artists` and `/artists/{slug}`: canonical `artist_type=person`.
- `/groups` and `/groups/{slug}`: canonical `artist_type in (group, orchestra, choir)`.
- MusicBrainz/SongChart domain entity remains `Artist`; relationships and IDs are unchanged.
- Canonical DB is authoritative; demo fallback exists only in local/testing.
- Legacy generic/singular public detail routes remain absent and do not redirect.

## Runtime authority
Full PHPStan/Pest/PostgreSQL/canonical verification must run in the Docker verification authority using `verify-songchart.bat`.

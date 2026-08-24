# Stage 17.5 Validation Report

Status: candidate evidence only. Canonical closure has not been claimed in this authoring environment.

## Implemented surface

- MusicBrainz Recording lookup/search capability;
- Recording normalization with duration, ISRCs, Artist Credit and Release appearance relationships;
- canonical Recording collision-safe slug mutation;
- ISRC external-identifier attachment;
- ordered `artist_recording` linking for already-imported canonical Artists;
- Admin MusicBrainz Recording search/import workbench;
- `/recordings` browse and `/recording/{slug}` detail shortcut;
- current-stage provider/use-case governance updates and focused tests.

## Validation performed in authoring environment

Static/syntax and executable repository-authority gates are run before packaging. Full Laravel/Pest/PHPStan/Pint/PostgreSQL/canonical closure remains target evidence only.

## Required target validation

1. apply the Stage 17.5 changeset to canonical-passed Stage 17.4;
2. restart `app` and `queue` (no migration required);
3. import Artist(s) first, then search/import a Recording from Admin → Providers → MusicBrainz;
4. confirm ISRC in Admin canonical identifiers and artist-credit link when the Artist existed;
5. confirm `/recordings` and `/recording/{slug}`;
6. run `verify-songchart.bat` once for canonical closure.

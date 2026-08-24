# Stage 17.6 Validation Report

Status: candidate evidence only. Canonical closure has not been claimed in this authoring environment.

## Implemented surface

- MusicBrainz Artist relationship/alias/selected URL enrichment;
- canonical group membership with temporal relationship metadata;
- minimal related Artist materialization from stable MusicBrainz identity evidence;
- Recording→Work normalization/materialization and Work MBID/ISWC identity;
- ordered Artist Credit credited-name/join-phrase persistence;
- Admin MusicBrainz Work search/import;
- `/works` public browse and canonical relationship rendering on entity detail pages;
- additive relationship metadata migration.

## Validation performed in authoring environment

PHP syntax and executable repository-authority/static verification gates are run before packaging. Full Docker/Pest/PHPStan/Pint/PostgreSQL canonical closure remains target evidence only.

## Required target validation

1. apply Stage 17.6 changeset to canonical-passed Stage 17.5;
2. run migrations;
3. restart app/queue;
4. re-import a group such as BLACKPINK and confirm member Artists + canonical relationships;
5. import a Recording and confirm Artist Credit join phrase plus linked/materialized Work/ISWC where supplied;
6. inspect `/artist/{slug}`, `/recording/{slug}`, `/works` and Admin catalog;
7. run `verify-songchart.bat` once for canonical closure.

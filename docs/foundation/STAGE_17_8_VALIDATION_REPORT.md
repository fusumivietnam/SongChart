# Stage 17.8 Validation Report

Status: candidate source validation; Docker canonical closure remains required.

## Implemented

- Provider-neutral authority policy and deterministic canonical field evidence resolver.
- Entity Passport read model using existing provenance, conflict, identifier and destination tables.
- Public plural canonical URL family for Artist, Release Group, Release, Recording, Work, Version and Collection.
- Removal of generic `/entity/{type}/{slug}` and singular public detail aliases without redirects.
- Shared URL mapper used by search/home/catalog surfaces.
- Executable tests for fusion selection and public URL guardrails.

## Runtime validation

1. Apply Stage 17.8.
2. No migration is required.
3. Open `/artists/{slug}`, `/releases/{slug}`, `/recordings/{slug}`, `/works/{slug}` and confirm detail rendering.
4. Confirm `/entity/artist/{slug}` and `/artist/{slug}` return 404.
5. Import canonical data from MusicBrainz and inspect Data Passport on entity detail.
6. Run `verify-songchart.bat` for canonical closure.

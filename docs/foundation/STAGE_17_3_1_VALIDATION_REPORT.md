# Stage 17.3.1 — Validation Report

Status: source candidate; authoritative Docker canonical closure pending on target.

## Implemented

- added dedicated `manage-catalog` authorization capability for editor/provider-manager/super-admin roles;
- added password-confirmed, audited canonical Artist update route;
- added Admin Artist edit form for name, sort name, slug, type, country, and verification state;
- kept external identifiers, relationships, conflicts, and provider evidence read-only;
- fixed enum-backed latest import rendering in Development Control Center;
- narrowed `database unavailable` reporting to actual database query failures.

## Validation performed in packaging environment

- PHP syntax sweep for changed application/tests PASS;
- focused catalog/development tests prepared for Docker target;
- official-source governance PASS;
- documentation and repository-state verification PASS;
- test-taxonomy and type-guardrail verification PASS;
- authentication/privileged-operations verification PASS;
- use-case/domain/repository-contract compiler verification PASS;
- verification command surface and candidate contract PASS;
- target Docker/Pest/PHPStan/canonical closure pending.

## Target smoke plan

1. open `/admin/catalog/artist/{id}` for an imported MusicBrainz Artist;
2. update slug/name/country with a rationale and confirm password if prompted;
3. confirm redirect back to Artist detail and public `/artist/{new-slug}` works;
4. inspect `/admin/audit` for `catalog.artist.updated`;
5. open `/development/status` and verify Pipeline snapshot shows counts/latest import instead of false `database unavailable`;
6. run `verify-songchart.bat`.

## Result

Stage 17.3.1 is ready for target verification after focused source gates pass in packaging.

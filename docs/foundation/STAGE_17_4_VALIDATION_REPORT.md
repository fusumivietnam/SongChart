# Stage 17.4 Validation Report

Status: candidate evidence only. Canonical closure has not been claimed in this authoring environment.

## Implemented surface

- distinct canonical `ReleaseGroup` model/entity and forward migration;
- nullable Release → Release Group canonical link;
- MusicBrainz Release Group lookup/search normalization and MBID identity;
- MusicBrainz Release lookup/search normalization with edition metadata;
- complete-date-only canonical date mutation with partial-date assertion preservation;
- Cover Art Archive front-image HTTPS reference retention when MusicBrainz advertises front artwork;
- Release Group/Release Admin provider workbench search/import;
- `/release/{slug}` public shortcut and live `/releases` copy;
- domain, use-case and schema-ownership authority updates;
- focused Feature tests for adapter, mutation and Admin workbench behavior.

## Validation performed in authoring environment

Passed before packaging:

- PHP syntax sweep over application/config/database/routes/tests/scripts PHP files;
- `verify-domain-contracts.php`;
- `verify-use-case-contracts.php`;
- `verify-schema-ownership.php`;
- `verify-provider-catalog-contracts.php`;
- `verify-provider-canonical-mutation.php`;
- `verify-provider-import-orchestration.php`;
- `verify-test-taxonomy.php`;
- `verify-type-guardrails.php`;
- `verify-migration-lifecycle.php`;
- `verify-laravel-alignment.php`;
- repository contract manifest compilation/verification.

The authoring full-source artifact does not include `vendor/`, so Laravel runtime/Pest/PHPStan/Pint/PostgreSQL migration execution and canonical Docker closure are intentionally not claimed here. `candidate-verification.json` remains `closure_ready=false`.

## Required target validation

1. apply the Stage 17.4 changeset to the Stage 17.3.3 target;
2. run `php artisan migrate` through Docker dev setup/runtime (the changeset itself must not invoke host PHP);
3. restart/recreate `app` and `queue` if needed;
4. smoke-test Admin MusicBrainz Release Group/Release search/import;
5. confirm `/releases` and `/release/{slug}` after an applied Release;
6. run `verify-songchart.bat` once for canonical closure.

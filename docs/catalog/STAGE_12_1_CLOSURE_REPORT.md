# Stage 12.1 — Catalog Closure Report

## Implemented safeguards

- `entity_matches.active_match_key` is unique and populated only for `matched` rows. Candidate, rejected, review and superseded records remain historical/evidentiary rows.
- `metadata_conflicts.normalized_pair_key` stores assertion IDs in sorted order. This makes A/B and B/A identical at the database boundary.
- `MetadataConflict` rejects an assertion conflicting with itself.
- Catalog regression tests cover active match cardinality, symmetric conflicts, self conflicts, collection position uniqueness, recording delete restriction and work null-on-delete.
- The catalog verifier now checks all canonical, provenance and mapping tables, important indexes, rollback methods, seeder registration and Composer quality-chain ownership.

## Runtime closure evidence

Stage 12 is complete only after the development baseline reports success for:

```bash
composer locks:verify
php artisan migrate:fresh --seed
php artisan test --filter=CanonicalCatalogModelTest
php artisan test --filter=CatalogInvariantHardeningTest
php artisan migrate:rollback --step=3
php artisan migrate
composer release:verify
```

The PostgreSQL CI job must also pass. Packaging-time static checks are not a substitute for these runtime results.

## Stage 13 readiness

Stage 13 may begin only when the commands above pass and both committed lockfiles are present in the release baseline.

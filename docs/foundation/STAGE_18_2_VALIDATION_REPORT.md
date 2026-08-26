# Stage 18.2 Validation Report — Public Search & Canonical Surfaces

Status: current-stage validation record; exact-tree verification pending.

## Scope currently implemented

Stage 18.2 starts from the accepted Stage 18.1 canonical/provider baseline and targets:

- PostgreSQL-backed deterministic public relevance ranking;
- query-wide facet counts independent from the selected display facet;
- deterministic public pagination and canonical SongChart URLs;
- provider-neutral public SearchCatalog/detail read boundaries;
- preservation of existing Artist/Group, Release, Recording and Work canonical surfaces.

## Verification required for the current tree

Run after pulling the latest Stage 18.2 branch:

- focused production-search PostgreSQL tests;
- `tests/Feature/SearchFlowTest.php`;
- `tests/Feature/SearchResultsTest.php`;
- `tests/Feature/PublicCatalog/PublicCatalogBrowseTest.php`;
- PHPStan/Larastan;
- `./songchart candidate --prepare` to refresh governed generated authority;
- `./songchart verify` only after candidate PASS.

## Security/data assessment

- Public search remains read-only.
- Search requests do not call provider APIs.
- Query values are bound parameters; repository-owned domain contracts provide SQL field identifiers.
- Search results use SongChart canonical type/slug routing instead of provider identities.
- No schema migration is introduced by the initial ranking slice.

## Closure rule

Do not mark Stage 18.2 accepted or package-ready until candidate and canonical verification pass on the exact current tree after all generated artifacts are committed.

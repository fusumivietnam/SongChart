# Phase 6 — Search Results

Status: implemented in SongChart 0.1.0-dev, Stage 06.

## Goal

Turn the Stage 04 search vertical slice into a governed, scalable result-reading experience without changing the canonical search boundary or introducing provider-backed ranking.

## Non-goals

- no live provider API calls;
- no autocomplete endpoint;
- no popularity, chart, listener or engagement ranking;
- no infinite scroll;
- no database-backed production catalog in this stage;
- no search analytics dashboard.

## Required composition

1. Query form and validated controls.
2. Entity facets with counts for the current query.
3. Result summary with visible range and total.
4. Canonical result list with explicit entity and verification state.
5. Server-rendered pagination with stable query parameters.
6. Related queries and result provenance.
7. Scoped empty state when the query has matches but the active facet does not.

## Application contract

`SearchCatalog::search()` returns the result page plus query-level metadata:

- `items`;
- `total` for the active facet;
- `total_all` for the whole query;
- `counts` per entity type;
- `page`, `per_page`, `last_page`, `from`, `to`;
- `related` queries.

Controllers and views continue to depend on `SearchCatalog`, never provider SDKs or controllers.

## URL contract

Supported parameters:

- `q` — maximum 100 characters;
- `type` — approved canonical entity type or `all`;
- `sort` — `relevance`, `title`, `year_desc`;
- `page` — integer from 1 to 1000.

Facet links preserve `q` and `sort` and reset pagination. Pagination preserves `q`, `type` and `sort`. A requested page beyond the result set returns 404 rather than silently showing an empty page.

## Accessibility and semantic rules

- Result list has an explicit accessible label.
- Facets use navigation semantics and `aria-current` for the active facet.
- Result summary is readable without relying on color.
- Pagination exposes previous/next relations.
- Reusable search forms on the same document must use unique input IDs. Header search and page search may not share an `id`.
- Verification state is always expressed as text; unverified results must not be represented only by absence of a badge.

## Test rules

Search-result tests must verify:

- facet counts and canonical URLs;
- query/sort preservation;
- range summary and pagination;
- scoped empty facet state;
- invalid page validation and out-of-range 404;
- explicit partial verification state;
- unique IDs for reusable search surfaces.

Continue following the scoped assertion rule from Stage 04. Do not use page-wide negative title assertions when the same title can appear in suggestions or surrounding content.

## Acceptance criteria

- Mixed queries expose entity counts.
- Active facets remain URL-addressable and preserve sorting.
- Result ranges and pagination are deterministic.
- Empty active facets do not hide matches in other entity types.
- Unverified results expose a visible partial-data state.
- No fabricated ranking or provider availability is introduced.
- Search result patterns remain represented in `/development/design-system/patterns`.

## Patch 01 — semantic pagination assertions

The visible pagination label contains nested markup (`<strong>` around the current page). Feature tests must not assert a flattened string such as `Trang 1 / 3`, because Blade may preserve the same accessible text while changing inline markup.

The pagination component exposes stable semantic markers:

- `data-current-page`;
- `data-last-page`;
- `rel="prev"` and `rel="next"`;
- preserved query parameters in canonical URLs.

Pagination tests verify those markers and URLs. They may separately verify human-readable labels, but must not use a single flat-text assertion across nested elements.

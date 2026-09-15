# Phase 4 — Complete Search Flow

Status: implemented in SongChart 0.1.0-dev, Stage 4.

## Task contract

### Goal

Deliver the first complete public vertical slice: search-first homepage, validated search request, canonical mixed-entity results, entity filtering/sorting, empty/error behavior, entity detail and explicit provider routing.

### Non-goals

- no live provider API calls;
- no autocomplete network endpoint;
- no popularity, chart, listener or engagement metrics;
- no provider playback or media storage;
- no production canonical catalog migration in this phase.

### Architecture

`App\Contracts\Search\SearchCatalog` is the application boundary. `DemoSearchCatalog` supplies deterministic local fixtures until the canonical PostgreSQL catalog is implemented. Controllers and views do not call providers and do not depend on provider SDKs.

Routes:

- `/` — search-first entry;
- `/search` — mixed entity results;
- `/entity/{type}/{slug}` — shared canonical entity detail.

Shared patterns:

- `components/search/form.blade.php`;
- `components/entity/result-row.blade.php`;
- `components/provider/chooser.blade.php`.

## Contract decisions

- Entity types remain explicit everywhere.
- Search supports `all`, `artist`, `recording`, `release`, `version`, `work`, `collection`.
- Sort supports relevance, title and newest year only.
- Available providers are actionable; unknown/stale providers are disabled.
- Every external provider action discloses that the user leaves SongChart.
- Missing verification uses the canonical partial-data message.
- Fixtures never imply chart rank, popularity or streaming availability.

## Required states

Implemented:

- initial/no-query;
- populated results;
- filtered results;
- validation error;
- empty results;
- partial canonical data;
- unknown/stale provider availability;
- entity not found.

Loading/degraded remote-provider states remain represented in `/development/design-system`; the current request is server-rendered and has no live remote dependency.

## Acceptance criteria

- Homepage follows the contract order: H1, description, large search, action, filters.
- Search input is validated and limited to 100 characters.
- Result rows expose title, entity type, context, metadata and detail action.
- Empty state includes query, broader filter and missing-content report.
- Entity detail includes breadcrumb, identity, metadata, provider chooser and provenance.
- Provider routing never claims universal availability.
- Search patterns are represented in `/development/design-system`.
- Feature tests cover the complete vertical slice.

## Regression testing rule — scoped assertions

Search pages can repeat entity names in unrelated regions such as suggestions, navigation, fixtures or explanatory copy. Feature tests must therefore verify the semantic output of the result list rather than asserting that a title is absent from the entire HTML document.

For filtered-result tests:

- assert the expected result count;
- assert the expected canonical detail URL is present;
- assert excluded canonical detail URLs are absent;
- do not use a page-wide `assertDontSee()` for labels that may legitimately appear outside the result list.

This rule prevents false failures when recommendation copy changes while the search filter itself remains correct.

## Cross-markup assertion rule

Do not assert one flattened sentence when the rendered sentence crosses nested HTML elements. Examples include pagination counters, result summaries with emphasized numbers, badges inside headings and labels containing icons. Assert stable semantic attributes, canonical URLs, accessible labels or individual text fragments instead. See `docs/setup/FEATURE_TEST_ASSERTIONS.md`.

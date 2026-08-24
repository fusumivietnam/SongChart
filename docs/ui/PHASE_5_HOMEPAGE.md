# Phase 5 — Homepage

Status: implemented in SongChart 0.1.0-dev, Stage 05.

## Goal

Turn the search-first entry into a complete governed homepage without weakening search prominence or introducing chart, popularity, autoplay or provider-playback behavior.

## Required hierarchy

1. Search-first hero above the fold.
2. Explicit entity entry points.
3. Canonical starting points with verification state.
4. Editorial discovery with visible provenance.
5. Clear explanation that SongChart routes to official providers rather than acting as a player.

## Architecture

Homepage discovery data comes from `SearchCatalog::homepage()`. Controllers and Blade templates must not embed independent catalog fixtures, query provider SDKs or invent engagement metrics.

Current local implementation uses `DemoSearchCatalog` deterministic fixtures. A future PostgreSQL catalog may replace the adapter without changing the homepage controller or view contract.

## Content rules

- Search remains the dominant first interaction.
- No promotional carousel above search.
- No chart rank, listener count, popularity score or fabricated trend.
- Entity type must remain explicit in every discovery card.
- Editorial content must identify its provenance.
- Provider positioning must state that users leave SongChart.
- Homepage links must route to canonical search/detail flows already covered by Stage 04.

## Reusable patterns

- `components/home/entity-entry.blade.php`
- `components/home/featured-card.blade.php`

Reusable homepage patterns must be represented in UI Preview before significant visual divergence is introduced.

## Test rules

Homepage tests should verify semantic section order and canonical URLs. Do not couple tests to decorative utility classes or entire generated HTML snapshots.

## Acceptance criteria

- Hero order continues to match the frontend design contract.
- Six supported entity entry points are visible.
- Featured canonical entities expose type and verification state.
- Editorial collection exposes provenance.
- Homepage contains no popularity or playback claims.
- Homepage data is supplied through `SearchCatalog`.
- Feature tests cover hierarchy, canonical links and prohibited claims.

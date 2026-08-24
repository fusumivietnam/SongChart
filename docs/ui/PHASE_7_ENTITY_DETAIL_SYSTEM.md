# Stage 07 — Entity Detail System

Status: implemented in SongChart 0.1.0-dev, Stage 07.

## Goal

Turn the initial shared entity page into a governed detail system for artist, recording, release, version, work and collection identities.

## Required composition

1. Canonical breadcrumb and stable entity URL.
2. Identity header with explicit entity and verification labels.
3. Entity-specific facts.
4. Typed canonical relationships.
5. External identifiers, or an explicit missing-identifiers state.
6. Provider chooser with availability disclosure.
7. Structured provenance with source status and checked date.

## Architecture

`SearchCatalog::find(type, slug)` remains the application boundary. It returns one normalized detail payload. Blade templates do not infer entity relationships, identifiers or provider state and do not contain per-entity fixture catalogs.

The system uses shared components:

- `components/entity/facts.blade.php`;
- `components/entity/relationships.blade.php`;
- `components/entity/identifiers.blade.php`;
- existing `components/entity/result-row.blade.php`;
- existing `components/provider/chooser.blade.php`.

Entity-specific differences are data-driven through `facts`, `relationship_title`, `relationships`, `identifiers` and `eyebrow`.

## Identity rules

- Artist, recording, release, version, work and collection remain distinct entity types.
- A work must not be presented as a recording.
- A version must link back to its work/recording context when known.
- Missing identifiers are visible; never fabricate an ISRC, MusicBrainz ID or provider ID.
- Verification state must be textual, not inferred from missing UI.

## Relationship rules

- Relationship cards always link to canonical SongChart entity URLs.
- Relationship labels and entity badges remain explicit.
- Empty relationships use a governed empty state.
- Blade must not calculate relationships from strings such as `context` or `meta`.

## Provider and provenance rules

- Provider availability is not canonical identity.
- Available, unknown and stale states remain visibly different.
- External actions retain the leave-SongChart disclosure.
- Provenance uses structured rows: source, status and checked date.
- Fixture provenance must say that it is fixture/internal data.

## Test rules

Entity detail tests verify stable semantic attributes, canonical relationship URLs, identifiers/missing states, provider states and provenance headings. Follow `docs/setup/FEATURE_TEST_ASSERTIONS.md`; do not assert decorative layout classes or flat text crossing nested markup.

## Non-goals

- no provider API calls;
- no playback;
- no production database repository;
- no edit/history UI;
- no invented credits, identifiers, availability or popularity metrics.

# Stage 12 — Canonical Identity & Catalog Data Model

## Canonical ownership

SongChart owns canonical ULIDs. Provider identifiers are external evidence and never primary keys.

### Entities

- `artists`: people, groups and other credited performers.
- `works`: abstract compositions.
- `recordings`: captured performances, optionally linked to a work.
- `releases`: published albums, singles, EPs or other editions.
- `release_tracks`: ordered release positions pointing to recordings.
- `recording_versions`: named variants such as album, live, acoustic or remix.
- `collections`: curated groupings with polymorphic `collection_items`.

Artist credits are represented by `artist_recording` and `artist_release` with role and position.

## Metadata provenance

- `metadata_sources` identifies provider, editorial or other evidence owners.
- `metadata_assertions` stores field-level values, fingerprints, confidence, observation time and verification state.
- `metadata_conflicts` links incompatible assertions without silently overwriting either value.
- `external_identifiers` links canonical entities to stable namespaces such as MusicBrainz IDs or ISRC.

Raw provider payload storage and retention are Stage 13 concerns.

## Provider identity mapping

Existing `provider_entities` remain provider-owned records. `entity_matches` links one provider entity to a canonical entity with:

- status: candidate, matched, needs_review, rejected or superseded;
- method;
- confidence;
- evidence.

`entity_relationships` records cross-entity facts with provenance and verification state.

## Constraints

- Canonical slugs are unique.
- External namespace/value pairs are globally unique.
- Release disc/track positions are unique.
- Collection positions and members are unique.
- Assertion identities include source and value fingerprint.
- Provider-to-canonical match tuples are unique.
- Canonical relationships are unique by subject, predicate and object.

Polymorphic canonical references use indexed `entity_type` + ULID pairs. Referential validity across those pairs is enforced by application validation and Stage 12 tests because SQL foreign keys cannot target multiple canonical tables.

## Fixtures

`CatalogFixtureSeeder` creates a deterministic Radiohead/OK Computer fixture, including:

- artist, work, recording, release and version;
- release ordering and artist credits;
- external identifier;
- two conflicting release-date assertions;
- provider entity match;
- verified relationship;
- editorial collection.

The fixture is idempotent and intended for development and tests, not as imported production truth.

## Validation

Run:

```bash
composer catalog:verify
php artisan test --filter=CanonicalCatalogModelTest
composer verify
```

## Stage 12.1 closure invariants

Only one active `matched` row may exist per provider entity; non-active candidates and history may coexist. Metadata conflict pairs are normalized so A/B and B/A are identical, and self-conflicts are rejected. See `STAGE_12_1_CLOSURE_REPORT.md`.

# Data Model Principles

This document is an overview. Cross-boundary representation is owned by `docs/project/domain/DATA_CONTRACT.md`; provider classification vocabulary is owned by `docs/providers/PROVIDER_TAXONOMY.md` and the typed provider contracts. When overview prose and a typed/machine authority differ, the typed/machine authority wins and this overview must be corrected rather than creating a compatibility dialect.

## Internal identity

Use ULID strings for SongChart internal entity identifiers.
Provider/external IDs are opaque alternate identities, never internal primary keys and never numeric identity surrogates.

Do not introduce UUIDv7 as a second internal identity convention unless a later governed migration explicitly changes the canonical data contract.

## Canonical entities

- artists
- artist_names
- releases
- recordings
- tracks
- works
- credits
- genres
- labels
- provider_entities
- external_links
- metadata_assertions
- merge_records

## Provider identity

`provider_entities` should contain:
- provider
- entity_type
- external_id
- canonical_url
- market
- raw_fingerprint
- fetched_at
- expires_at
- status

Unique constraint:
`provider + entity_type + external_id + market`.

Provider `category`, operational `role`, and `capability` are distinct concepts. Do not reuse a role such as `destination` as a category, or invent provider-specific capability spellings when a typed capability code already owns the meaning.

Legacy fixtures, seeders and application code must converge toward current typed provider/data contracts when touched. Do not widen current enums merely to preserve an obsolete synonym when the old value represented a different concept.

## Provenance

Important metadata fields need:
- source/provider
- source record ID
- retrieved timestamp
- confidence
- verification status
- editor override
- audit history

Representation of unknowns, timestamps, partial dates, URLs, booleans, collections and machine reason codes follows `docs/project/domain/DATA_CONTRACT.md`.

## Slugs

- Slug is presentation, not identity.
- Unicode input is normalized.
- Public slug uses deterministic ASCII transliteration where practical.
- Collision resolved by stable suffix.
- Old slugs are retained in redirect history.
- Canonical URL is emitted for all indexable pages.

## Database rules

- Foreign keys by default.
- Unique constraints encode invariants.
- Partial indexes for active records where useful.
- JSONB only for provider payload snapshots or genuinely flexible metadata.
- Frequently queried fields must not be hidden in JSONB.
- A repeated JSON/config key that becomes cross-provider policy or runtime behavior must be promoted to a typed owner instead of copied into multiple blobs.
- Persisted cross-module states should use an existing typed enum/code owner when one exists; tests and seeders should consume that owner rather than create free-form synonyms.

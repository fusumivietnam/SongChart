# Data Model Principles

## Internal identity

Use UUIDv7/ULID internal identifiers.
Provider IDs are alternate identities, never internal primary keys.

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

## Provenance

Important metadata fields need:
- source/provider
- source record ID
- retrieved timestamp
- confidence
- verification status
- editor override
- audit history

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

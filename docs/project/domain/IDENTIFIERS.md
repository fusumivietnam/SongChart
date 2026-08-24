# Identifier Contract

Status: domain identifier authority.

## Internal identity

Canonical entities use ULID primary keys. PHP representation is `string`. Canonical emitted representation is lowercase. Route input is case-insensitive and uses `[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}` where an internal ID is intentionally exposed to an admin route.

Provider identifiers never replace canonical primary keys; they live in provenance/mapping structures such as `external_identifiers` and provider entity mappings.

## Slug identity

Slugs are presentation identifiers, not primary identity. Implemented public entity lookup currently uses lowercase ASCII/hyphen slugs matching `[a-z0-9-]+`.

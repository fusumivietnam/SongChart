# Canonical Field Naming

Status: domain naming authority.

- Person/group-like display identity: `name`.
- Recording-version display identity: `name`.
- Work, recording, release and collection display identity: `title`.
- URL presentation identity: `slug`.
- Duration: `duration_ms`; unit is milliseconds.
- Release calendar date: `released_on`; only entities declaring this field may read it.
- Verification lifecycle: `verification_state`.
- Provider/external identity is stored outside canonical primary keys.

Do not introduce synonyms such as `display_name`, `recording_name`, `release_date`, `duration_seconds`, `externalId`, or `providerId` without an explicit contract revision and use-case justification.

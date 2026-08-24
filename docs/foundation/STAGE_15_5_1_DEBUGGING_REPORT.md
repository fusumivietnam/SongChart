# Stage 15.5.1 Debugging Report

## Observed failure

PostgreSQL rejected the homepage catalog query because `recording_versions` was ordered by a non-existent `title` column.

## Root cause

`EloquentSearchCatalog` assumed every canonical entity except artists used `title`. Recording versions use the native `name` column in both the model and canonical catalog migration. The same assumption also affected result serialization.

## Minimal corrective change

A single `titleColumn()` mapping now owns the search/display field selection:

- artist and version: `name`
- work, recording, release, collection: `title`

## Behavior intentionally unchanged

No schema, route, pagination, search ranking, identity, authentication, or provider behavior changed.

## Regression coverage

`SearchFlowTest` now creates a recording version and verifies that version search reads and renders its native `name` field.

## Verification boundaries

PHP syntax was verified in the packaging environment. Pint, Larastan, SQLite, PostgreSQL, and the full release matrix are delegated to the Laragon installer.

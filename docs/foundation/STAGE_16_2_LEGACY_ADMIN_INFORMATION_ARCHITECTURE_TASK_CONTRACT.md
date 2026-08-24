# Stage 16.2 Task Contract — Admin Information Architecture

## Goal
Turn the admin shell into a navigable, operations-first information architecture backed by real database state.

## In scope
- Read-only admin routes for catalog, providers, imports, quarantine, identity conflicts, users, and system health.
- A compact sidebar containing only working links.
- Schema-safe operational snapshots for empty or partially migrated databases.
- Authorization through the existing shared admin middleware boundary.
- Feature coverage for route protection, rendering, and navigation.

## Out of scope
- Catalog writes.
- Provider import actions.
- Quarantine retry actions.
- Identity conflict decisions.
- User role or activity mutations.
- New permissions or database migrations.

## Acceptance
- Every Stage 16.2 route is protected by the existing admin middleware stack.
- Every page renders against an empty database after migrations.
- Navigation contains no disabled placeholder modules.
- Data shown is runtime data, not fabricated dashboard content.

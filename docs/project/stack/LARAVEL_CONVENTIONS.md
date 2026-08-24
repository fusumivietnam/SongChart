# Laravel Conventions

## Controllers

Controllers coordinate validated and authorized requests. They do not own multi-write business logic, provider calls or long-running work.

## Form Requests and authorization

Use Form Requests for request shape and input validation. Use Policies or named Gates for authorization. Hiding controls in Blade is not authorization.

## Actions

Actions represent application use cases and orchestration. Use a transaction when one invariant spans multiple writes. Do not create one Action for trivial model assignment without a boundary benefit.

## Models

Models may contain relationships, casts, query scopes and small lifecycle invariants. They must not perform provider HTTP calls, response rendering or multi-aggregate orchestration.

ULID-backed models use `HasUlids`. Bounded states use enums and matching model casts.

## Jobs and events

Jobs are idempotent and retry-safe. Events represent completed facts, not commands. Provider failures must degrade without corrupting canonical data.

## Integrations

Provider adapters use Laravel HTTP client, immutable DTOs and explicit capabilities. Secrets come from configuration/environment. Raw payload retention follows provider policy.

## Database

Migrations include indexes, constraints and rollback. Validate on SQLite and PostgreSQL. Do not use provider identifiers as canonical primary keys.

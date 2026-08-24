# ADR-003: Laravel-native application boundaries

## Status

Accepted.

## Context

SongChart needs stable authentication, authorization, validation, queue, cache and operational foundations. Reimplementing framework infrastructure creates duplicate behavior and increases maintenance risk.

## Decision

Use Laravel-native application boundaries by default:

- Gates and Policies for authorization;
- Form Requests for HTTP validation and normalization;
- Fortify for authentication and two-factor flows;
- Jobs, events, notifications, scheduler, cache, rate limiter, HTTP client and filesystem when those capabilities are required.

Custom code is reserved for SongChart domain behavior and provider/extension governance. A custom abstraction requires a concrete domain reason or at least two real implementations.

## Consequences

- Framework behavior remains recognizable and testable.
- Duplicate middleware and inline validation are removed when equivalent Laravel boundaries exist.
- Upgrades depend on documented Laravel contracts rather than private infrastructure.
- Domain-specific managers and policies remain custom where Laravel does not define the business rules.

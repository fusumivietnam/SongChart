# Observability and Debug Rules

## Tool responsibilities

### Sentry

- exceptions;
- performance traces;
- slow queries/N+1 signals;
- release/environment correlation;
- sampled session replay after privacy review.

### Laravel Pulse

- application-level operational overview;
- requests;
- slow jobs;
- queues;
- usage/card views appropriate for internal operators.

### Laravel Telescope

- local and restricted staging debugging;
- requests, jobs, mail, queries and events.

Telescope must not be publicly reachable in production.

## Data hygiene

Before sending telemetry:
- redact authorization/cookie headers;
- redact tokens and OTPs;
- scrub personal payload fields;
- avoid raw provider responses;
- avoid full email bodies;
- define retention and sampling.

## Business versus technical dashboards

Business admins see:
- failed user-facing workflows;
- content sync impact;
- email delivery status in plain language;
- provider availability.

System operators see:
- traces;
- queue/job identifiers;
- exception details;
- SQL/query diagnostics;
- deployment release.

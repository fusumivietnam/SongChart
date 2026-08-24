# Stage 11 Task Contract — Foundation Alignment & Architecture Guardrails

## Goal

Align the completed Stage 10 source with the existing Laravel-first architecture and add executable guardrails that prevent duplicate infrastructure patterns.

## Non-goals

- no new catalog or discovery feature;
- no new package or framework;
- no schema change;
- no provider API call;
- no visual redesign;
- no replacement documentation hierarchy.

## Acceptance criteria

- existing authoritative documentation is read and retained;
- admin access uses Laravel authorization through the `access-admin` Gate;
- obsolete custom admin middleware is removed;
- extension write endpoints use dedicated Form Requests;
- admin controllers extend the shared controller foundation;
- exception details are logged but not exposed to admin responses;
- architecture regression tests cover the new boundaries;
- one Composer verification command runs backend tests, formatting checks, static analysis and the frontend build;
- project status and affected documentation are reconciled.

## Affected modules

- application foundation;
- admin authorization;
- extension administration HTTP boundary;
- test and verification tooling;
- project documentation.

## Data/provider/policy impact

No database or provider contract changes. Extension package validation remains governed by the existing extension lifecycle and preflight documents.

## Tests

- admin authentication and authorization Feature tests;
- controller foundation regression tests;
- Stage 11 architecture guardrail tests;
- complete existing test suite;
- Pint, Larastan/PHPStan and Vite build through `composer verify`.

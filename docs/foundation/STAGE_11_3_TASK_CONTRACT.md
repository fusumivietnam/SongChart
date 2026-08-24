# Stage 11.3 Task Contract

Product: `SongChart 0.1.0-dev`  
Stage: `11.3 — Laravel Feature Alignment Audit`  
Role: task contract.

## Goal

Audit current application infrastructure against Laravel 13 and Fortify boundaries, record where framework-native features are already used, identify justified SongChart-specific code, and add an executable regression check that prevents accidental duplicate infrastructure.

## Non-goals

- No new product feature.
- No database migration or schema change.
- No provider integration.
- No role/permission package.
- No broad refactor solely for naming or folder symmetry.
- No replacement of extension lifecycle code that encodes SongChart package policy.

## Acceptance criteria

- A feature-alignment matrix covers authentication, authorization, validation, queues, events, cache, rate limiting, notifications, HTTP client, filesystem, scheduler, encryption and logging.
- Every finding is classified as aligned, accepted custom, deferred, or remediation candidate.
- A standalone verifier detects known duplicate-infrastructure patterns without requiring application boot.
- `composer alignment:verify` is part of `composer verify`.
- Documentation index, Stage 11 status and delivery manifest reflect Stage 11.3.
- Full-source and Windows-compatible change-set archives are produced.

## Affected ownership

- Foundation governance and quality gates.
- No runtime module ownership changes.

## Data, provider and policy impact

None. This audit records current implementation only.

## Tests and verification

- Run `php scripts/verify-laravel-alignment.php`.
- Run `php scripts/verify-documentation.php`.
- Run PHP syntax lint for changed PHP files.
- Run `composer verify` on a dependency-installed target environment.

## Documentation impact

Update the audit, documentation index, Stage 11 implementation status, README, START_HERE and change manifest.

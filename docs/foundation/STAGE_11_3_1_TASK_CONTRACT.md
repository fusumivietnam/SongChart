# Stage 11.3.1 Task Contract

Product: `SongChart 0.1.0-dev`  
Stage: `11.3.1 — Quality Gate Compatibility & Pint Baseline Normalization`  
Role: task contract.

## Goal

Restore a trustworthy `composer verify` baseline by aligning Larastan/PHPStan configuration with PHPStan 2.x, normalizing repository-owned PHP through Laravel Pint, and preventing delivery artefacts from being packaged as application source.

## Non-goals

- No product feature or runtime behavior change.
- No schema, route, provider or authorization change.
- No broad refactor beyond formatter output and quality-gate compatibility.
- No suppression of real static-analysis findings solely to make the gate green.

## Acceptance criteria

- Removed PHPStan 1.x-only `checkMissingIterableValueType`.
- `composer quality:normalize` applies Pint to repository-owned PHP.
- `composer quality:verify` runs documentation, Laravel-alignment, source-package hygiene, Pint and Larastan checks.
- Full `composer verify` invokes the quality baseline before tests and frontend build.
- `payload/` and other change-set-only artefacts are rejected by source-package verification and removed by the Stage 11.3.1 change-set.
- Documentation and delivery manifests describe the compatibility change and local verification requirement.

## Affected ownership

Foundation quality gates and release packaging only.

## Tests and verification

- `php scripts/verify-source-package.php`
- `composer quality:normalize`
- `composer quality:verify`
- `composer verify` on a dependency-installed Laragon target

## Documentation impact

Update `AGENTS.md`, `START_HERE.md`, documentation index, Stage 11 implementation status, testing authority and the Stage 11 change manifest.

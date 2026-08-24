# Stage 11.10 Foundation Release Closure

Status: closure automation implemented; final closure is established by target-machine evidence.

## Closure changes

- `composer.lock` and `package-lock.json` are mandatory release artefacts.
- `locks:verify` validates both lockfile structures.
- `foundation:release` remains the strict structural gate.
- `release:verify` runs the complete quality, test, frontend build and release-readiness chain.
- GitHub Actions uses `composer validate --strict` and `npm ci` only.
- The temporary frontend dependency fallback has been removed.

## Why lockfiles are generated on the target machine

Dependency resolution requires access to Composer and NPM registries. Delivery tooling must not invent, hand-edit or copy unrelated lockfiles. The Stage 11.10 finalizer therefore resolves the current manifests using the target development environment, then immediately validates and tests the result.

## Required review after generation

- inspect package additions, removals and version changes in `composer.lock`;
- inspect package additions, removals and version changes in `package-lock.json`;
- confirm no unexpected registry, repository or local path is recorded;
- run security audits and classify any advisory;
- commit both lockfiles together with the Stage 11.10 source changes.

## Closure evidence

Stage 11 is closed when the following all pass from a clean working tree:

```bash
composer validate --strict
composer locks:verify
composer release:verify
composer foundation:release
```

The repository-host PostgreSQL job and frontend CI job must also complete successfully before treating the commit as a release candidate.

## Next stage

After closure evidence is recorded, begin Stage 12 — Canonical Identity and Catalog Data Model. Provider data import remains deferred until the Stage 13 ingestion pipeline.

## Windows finalizer

Run `scripts\finalize-stage-11-release.bat C:\laragon\www\songchart` from the project root or call the matching PowerShell script directly.

- NPM 12 remote tarball compatibility: the release finalizer sets `npm_config_allow_remote=all` only for its child npm commands and restores the previous environment value afterward.

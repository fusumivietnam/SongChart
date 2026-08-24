# Release Baseline Status

Status: executable release-baseline policy.

## Authority

A distributable SongChartWeb full-source baseline is release-reproducible only when all of the following are true:

- `composer.lock` exists and matches `composer.json`;
- `package-lock.json` exists and matches `package.json`;
- `composer locks:verify` passes;
- `composer canonical:verify` passes on the target development machine;
- `composer release:package` passes from the export tree;
- the archive excludes `vendor/`, `node_modules/`, `.git/`, `.changeset-backups/`, `payload/`, and build scratch directories.

## Packaging-environment limitation

The Stage 16.5.3 packaging environment does not provide Composer or dependency-network resolution. It therefore does not generate or fabricate lockfiles. Missing lockfiles remain an explicit target-machine release blocker rather than being replaced with guessed dependency state.

## Target-machine closure

On the development machine, generate/reconcile lockfiles using the approved dependency workflow, review the dependency diff, then run:

```powershell
composer validate --strict
composer locks:verify
composer canonical:verify
composer release:package
```

After all gates pass, export a distributable archive with:

```powershell
composer release:package
```

The provenance-gated package command refuses to package a baseline when canonical evidence or dependency authority is invalid.

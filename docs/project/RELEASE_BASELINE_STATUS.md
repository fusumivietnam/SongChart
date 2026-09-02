# Release Baseline Status

Status: executable release-baseline policy.

## Authority

A distributable SongChart release baseline is release-reproducible only when all of the following are true:

- `composer.lock` exists and matches `composer.json`;
- `package-lock.json` exists and matches `package.json`;
- lockfile authority passes;
- exact-tree candidate verification passes;
- canonical verification passes in the repository Docker verification environment through `composer canonical:verify`;
- `composer release:package` passes from that canonically verified source tree;
- release artifacts exclude `vendor/`, `node_modules/`, `.git/`, local backup/scratch directories, and mutable runtime state;
- product version/build identity follows `docs/project/release/versioning-policy.json`; unreleased source must not invent a release version.

## Dependency authority

Lockfiles are never fabricated by a packaging environment or repository API edit. Dependency changes use Composer/npm in the approved development workflow, are reviewed as source changes, and become part of the exact Git tree that is later verified.

## Closure

From Linux/WSL2 or GitHub Codespaces:

```bash
./songchart candidate --prepare   # only when generated authority needs refresh
./songchart candidate
./songchart verify
```

`./songchart verify` is the governed host entrypoint; inside the canonical Docker lane it executes the canonical closure owned by `composer canonical:verify`.

After canonical/provenance PASS, create the release/deployment artifact with:

```bash
composer release:package
```

The provenance-gated package command refuses to package a baseline when canonical evidence, source identity, generated authority, version/build identity, or dependency authority is invalid. Release packaging is not a development handoff mechanism; GitHub remains the source of truth between devices and AI execution environments.

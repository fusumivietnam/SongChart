# Release Baseline Status

Status: executable release-baseline policy.

## Authority

A distributable SongChartWeb release baseline is release-reproducible only when all of the following are true:

- `composer.lock` exists and matches `composer.json`;
- `package-lock.json` exists and matches `package.json`;
- lockfile authority passes;
- exact-tree candidate verification passes;
- canonical verification passes in the repository Docker verification environment;
- `composer release:package` passes from that canonically verified source tree;
- release artifacts exclude `vendor/`, `node_modules/`, `.git/`, local backup/scratch directories, and mutable runtime state.

## Dependency authority

Lockfiles are never fabricated by a packaging environment. Dependency changes use the approved development workflow, are reviewed as source changes, and become part of the exact Git tree that is later verified.

## Closure

From Linux/WSL2 or GitHub Codespaces:

```bash
./songchart candidate --prepare   # only when generated authority needs refresh
./songchart candidate
./songchart verify
```

After canonical/provenance PASS, create the release/deployment artifact with:

```bash
composer release:package
```

The provenance-gated package command refuses to package a baseline when canonical evidence, source identity, generated authority, or dependency authority is invalid. Release packaging is not a development handoff mechanism; GitHub remains the source of truth between devices and AI execution environments.

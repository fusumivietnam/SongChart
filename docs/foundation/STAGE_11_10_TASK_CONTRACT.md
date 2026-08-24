# Stage 11.10 Task Contract — Foundation Release Closure

Status: implemented; target-machine lock generation and release verification required.

## Objective

Close Stage 11 with reproducible PHP and frontend dependency graphs, strict CI installation behavior and one executable release gate.

## In scope

- generate `composer.lock` from `composer.json` on the target development machine;
- generate `package-lock.json` from `package.json` on the target development machine;
- require `composer install` and `npm ci` in CI;
- remove the transitional frontend `npm install` fallback;
- add release-lock validation;
- add an explicit `release:verify` Composer command;
- update the Stage 11 status and roadmap authorities;
- provide an installer/finalizer that creates lockfiles before running the strict release gate.

## Out of scope

- dependency upgrades outside the existing version constraints;
- application behavior, schema, routes, provider adapters or UI features;
- production deployment configuration;
- Stage 12 catalog entities.

## Acceptance criteria

1. `composer.lock` and `package-lock.json` exist after the target-machine finalizer runs.
2. `composer locks:verify` passes.
3. GitHub Actions uses `composer validate --strict` and `npm ci` without fallback.
4. `composer foundation:release` passes.
5. `composer release:verify` passes on the target development machine.
6. Documentation identifies Stage 11 as closed only after criteria 1–5 are observed.
7. Full-source delivery never contains `vendor/`, `node_modules/`, `.changeset-backups/` or change-set payload artefacts.

## Target-machine commands

```bash
composer update --no-install --no-interaction --prefer-dist
npm install --package-lock-only --ignore-scripts --no-audit --no-fund
composer install --no-interaction --prefer-dist
npm ci --no-audit --no-fund
composer release:verify
```

The Stage 11.10 change-set finalizer runs this sequence. Review both lockfiles before committing them.

## Windows finalizer

```bat
scripts\finalize-stage-11-release.bat C:\laragon\www\songchart
```

- NPM 12 remote tarball compatibility: the release finalizer sets `npm_config_allow_remote=all` only for its child npm commands and restores the previous environment value afterward.

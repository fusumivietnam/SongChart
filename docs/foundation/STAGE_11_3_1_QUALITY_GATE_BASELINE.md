# Stage 11.3.1 Quality Gate Baseline

## Compatibility decision

The repository uses `larastan/larastan ^3.8`, which targets PHPStan 2.x. The removed PHPStan 1.x parameter `checkMissingIterableValueType` must not appear in `phpstan.neon`. Missing iterable value types are treated as real findings and should be fixed with precise PHPDoc or native types rather than globally suppressed.

## Pint baseline

`pint.json` remains the formatting authority. The baseline command is:

```bash
composer quality:normalize
```

This runs Laravel Pint against repository-owned PHP. Formatter-only changes do not authorize unrelated refactors.

## Verification chain

```bash
composer quality:verify
composer verify
```

`quality:verify` checks documentation governance, Laravel feature alignment, source-package hygiene, Pint and Larastan. `verify` additionally boots Laravel, lists routes, runs Pest and builds frontend assets.

## Source-package hygiene

The full-source archive must not contain delivery-only folders such as `payload/`, `.changeset-backups/`, `vendor/` or `node_modules/`. Change-set payload belongs only inside the change-set archive.

## Local execution requirement

Pint and Larastan require Composer dependencies. A packaging environment without `vendor/` may validate PHP syntax and standalone verifiers, but must not claim the dependency-backed quality gate passed. The Laragon target is the release authority for `composer verify`.

### Source verification modes

- `composer source:verify` validates a development working tree and permits installed dependencies and local change-set backups.
- `composer delivery:verify` validates a distributable full-source package and rejects `vendor/`, `node_modules/`, `.changeset-backups/`, and `payload/`.

## Larastan Symfony Console compatibility patch

- Renamed the custom `extension:rollback --version` option to `--release`. Symfony Console already owns the global `--version` option, and the duplicate name caused Larastan/PHPStan to fail while reflecting the command.
- Added `ConsoleCommandSignatureTest` to prevent the option collision from returning.

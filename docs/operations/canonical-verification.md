# Canonical Verification Environment

## Purpose

This environment is the release/candidate verification authority. It exists so Pint, Larastan/PHPStan, Pest, PostgreSQL migrations/tests and frontend builds run before packaging with the actual locked dependency graph.

It does not replace Laragon for daily Windows development and it does not define production deployment topology.

## Runtime

- PHP 8.5
- Composer 2
- Node 22
- PostgreSQL 18.4 container; release guard requires major 18 exactly
- Redis 7.4 profile
- application source bind-mounted at `/workspace`
- `vendor/`, `node_modules` and Composer cache isolated in named Docker volumes

## Windows

Docker Desktop with WSL2 integration must be running.

```bat
verify-songchart.bat
```

PowerShell entry point:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/verify-canonical.ps1
```

Use `-NoBuild` only when the verification image has already been built and the Dockerfile did not change. Use `-KeepServices` only for diagnostics.

## Linux / WSL2

```bash
bash scripts/verify-canonical-host.sh
```

## Verification order

1. reset ephemeral PostgreSQL/Redis containers;
2. build the PHP 8.5 verification image;
3. restore Composer dependencies from `composer.lock` into the isolated vendor volume;
4. restore frontend dependencies from `package-lock.json` into the isolated node_modules volume;
5. verify PostgreSQL major 18;
6. run `composer quality:normalize` using the locked Pint binary;
7. run `composer canonical:verify`;
8. record candidate evidence;
9. re-check candidate contract.

The source tree is intentionally writable because the exact source emitted by Pint is the source that must later be packaged.

## Database lifecycle

The verification PostgreSQL database is ephemeral. `docker compose down` removes its container and test data. Dependency/cache volumes are preserved unless explicitly removed with Docker volume commands.

Do not point canonical verification at the Laragon development database.

## PHP extension image contract

The PHP 8.5 base image already provides core/runtime extensions including `curl`, `dom`, `mbstring`, and `xml`. The verification Dockerfile must not rebuild those extensions. SongChart compiles only the additional extensions needed by the canonical runtime and verifies the complete required extension set during image build and again at canonical verification startup.

Use:

```powershell
docker compose -f compose.verify.yml build --no-cache verify
docker compose -f compose.verify.yml run --rm --no-deps verify php scripts/verify-canonical-php-extensions.php
```

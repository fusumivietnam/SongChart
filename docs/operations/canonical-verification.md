# Canonical Verification Environment

## Purpose

This environment is the release/candidate verification authority. It exists so Pint, Larastan/PHPStan, Pest, PostgreSQL migrations/tests and frontend builds run before release packaging with the actual locked dependency graph.

It does not define production deployment topology.

## Runtime

- PHP 8.5
- Composer 2
- Node 24
- PostgreSQL 18.4 container; release guard requires major 18 exactly
- Redis 7.4
- application source bind-mounted at `/workspace`
- `vendor/`, `node_modules` and Composer cache isolated in named Docker volumes

## Supported host workflow

Linux/WSL2 with Docker Engine + Compose v2 is the supported local host. GitHub Codespaces is a remote adapter over the same repository contract.

Candidate closure:

```bash
./songchart candidate
```

Canonical closure after candidate PASS:

```bash
./songchart verify
```

For direct diagnostics of the host adapter, the Linux helper remains available:

```bash
bash scripts/verify-canonical-host.sh
```

Native Windows Batch/PowerShell and Laragon verification entrypoints are retired.

## Verification order

1. reset ephemeral PostgreSQL/Redis verification containers;
2. build the PHP 8.5 / Node 24 verification image;
3. restore Composer dependencies from `composer.lock` into the isolated vendor volume;
4. restore frontend dependencies from `package-lock.json` into the isolated node_modules volume;
5. verify the canonical PHP extension and PostgreSQL 18 contracts;
6. refresh exact-tree generated repository authority where the governed workflow requires it;
7. run `composer quality:normalize` using the locked Pint binary;
8. run `composer canonical:verify` exactly once;
9. record candidate evidence and re-check candidate contract.

The source tree is intentionally writable during canonical normalization because the exact normalized tree must match recorded canonical evidence.

## Database lifecycle

The verification PostgreSQL database is isolated from development state. The governed Compose lifecycle removes ephemeral verification containers while dependency/cache volumes may be retained for performance.

Never point canonical verification at a development or shared-demo database.

## PHP extension image contract

The PHP 8.5 base image already provides core/runtime extensions including `curl`, `dom`, `mbstring`, and `xml`. The verification Dockerfile must not rebuild those extensions. SongChart compiles only additional extensions required by the canonical runtime and verifies the complete required extension set during image build and canonical startup.

Useful diagnostic commands:

```bash
docker compose -p songchart-verify -f compose.verify.yml build --no-cache verify
docker compose -p songchart-verify -f compose.verify.yml run --rm --no-deps verify php scripts/verify-canonical-php-extensions.php
```

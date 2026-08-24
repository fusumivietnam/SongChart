# Laragon Runtime Setup

`vendor/` and `node_modules/` are intentionally excluded from the source archive.

They must be created on the target machine because they depend on:

- PHP version and enabled extensions;
- Composer platform resolution;
- Node.js/npm version;
- Windows/Linux filesystem behavior;
- native package binaries;
- current lockfiles.

## One-command setup

Open Laragon Terminal in the project directory:

```bat
scripts\setup-laragon.bat
```

The script performs:

1. PHP/Composer/Node validation.
2. `composer install`.
3. `.env` creation and application key generation.
4. `npm ci` when `package-lock.json` exists, otherwise `npm install`.
5. `npm run build`.
6. migrations and seeders.
7. storage link.
8. Laravel, route and Design Lab tests.

## Development mode

```bat
scripts\dev-laragon.bat
```

Laragon serves the `public/` directory. Vite and the queue worker run in separate terminals.

## Actual Design Lab screenshots

After runtime setup:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/screenshot-design-lab.ps1
```

Screenshots are generated from the real Laravel routes and real Vite build:

```text
storage/app/design-lab-screenshots/
```

## Release archive policy

Do not commit or distribute:

```text
vendor/
node_modules/
.env
storage/logs/
```

A deployable release may contain compiled assets under:

```text
public/build/
```

but PHP dependencies should still be installed using:

```bash
composer install --no-dev --classmap-authoritative
```

## Reproducibility

Commit these files after dependency resolution on the development machine:

```text
composer.lock
package-lock.json
```

Then future installations use:

```bash
composer install
npm ci
```

Do not manually edit either lockfile.

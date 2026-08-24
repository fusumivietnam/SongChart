# Testing Strategy

## Layers

- Unit: domain rules, normalizers, slugging, mapping.
- Feature: HTTP, Livewire, auth, policies, database behavior.
- Contract: provider fixtures and schema mapping.
- Integration: real infrastructure in CI where valuable.
- Browser: only critical user journeys.

## Critical suites

- canonical identity merge;
- slug collision and redirects;
- provider rate-limit/error mapping;
- provenance preservation;
- authorization;
- SEO metadata and structured data;
- queue idempotency;
- webhook replay protection.

## Fixtures

Provider fixtures:
- are sanitized;
- include captured date/API version;
- exclude tokens and personal data;
- cover missing, deprecated and unexpected fields.

No provider integration is complete with only a happy-path test.

## Foundation quality gates

Before the full suite, run:

```bash
composer quality:normalize
composer quality:verify
```

`quality:verify` is the authoritative formatter/static-analysis gate. Do not add obsolete PHPStan parameters or broad ignores to bypass findings. Full-source delivery must also pass `composer source:verify`.

### Source verification modes

- `composer source:verify` validates a development working tree and permits installed dependencies and local change-set backups.
- `composer release:package` validates a distributable full-source package, requires both dependency lockfiles and current-stage governance authorities, and rejects `vendor/`, `node_modules/`, `.changeset-backups/`, and `payload/`.


## Stage 11.7 executable suites

The repository must register all three PHPUnit suites: `Unit`, `Architecture`, and `Feature`. Run them with:

```bash
composer test:unit
composer test:architecture
composer test:feature
composer test:postgres
```

GitHub Actions separates PHP quality, PostgreSQL authority, and frontend production build jobs. See `docs/foundation/STAGE_11_7_TESTING_CI_MODEL.md`.

Dependency lockfiles are mandatory for reproducible releases and CI uses strict lockfile installation. `npm ci` is the only approved CI/frontend dependency installation path; there is no `npm install` fallback.

## Documentation reconciliation guardrail

`composer docs:verify` also enforces one current-development-stage heading in `README.md`. Historical stage markers belong in task contracts and change manifests, not as competing current-state headings in the repository entry point.


## Foundation closure commands

Run the structural audit with:

```bash
composer foundation:audit
```

Before a reproducible release candidate, run:

```bash
composer foundation:release
```

The release mode intentionally fails while mandatory dependency lockfiles are missing. A passing audit is not equivalent to a passing release gate.

## Release closure gate

Run from a clean working tree with committed lockfiles:

```bash
composer locks:verify
composer canonical:verify
composer foundation:release
```

CI must use `composer validate --strict`, `composer install` and `npm ci`. Do not restore the transitional `npm install` fallback.

## Stage 16.8.2 PostgreSQL test authority

PostgreSQL is the only release-authoritative database test engine for development closure, CI and production parity.

```bash
composer test
composer test:postgres
composer database:verify
composer canonical:verify
```

`TEST_PGSQL_DATABASE` defaults to `songchart_test`, never falls back to development `DB_DATABASE`, and is protected by the test-database safety marker. Credentials may fall back to the corresponding development credential when explicitly safe.

SQLite remains available only as `composer test:sqlite:compat` for optional diagnostics; it is not a CI or release gate.

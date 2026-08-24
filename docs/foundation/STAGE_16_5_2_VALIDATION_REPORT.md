# Stage 16.5.2 Validation Report

Status: implementation candidate v20; target canonical closure pending.

## Implemented

- one framework-neutral `RepositoryContractResolver`;
- authority → source → fingerprint → consumer graph;
- deterministic compiled repository contract manifest;
- semantic Composer checks for database and release authorities;
- forbidden raw authority literal scanning;
- impact resolver for changed authority/consumer paths;
- `songchart:doctor --contract=<authority>` diagnostics;
- PostgreSQL schema snapshot artifact;
- installed Spatie migration adaptation snapshot/verification;
- exact source-tree + Composer/npm lockfile fingerprints in canonical evidence;
- post-canonical artifact provenance verifier and verified source packager;
- architecture tests consuming authorities through the resolver instead of copying literals.

## Packaging environment limitations

The historical implementation-source baseline does not contain `composer.lock`, `package-lock.json`, `vendor/` or Docker/PostgreSQL runtime. Therefore packaging-side verification cannot truthfully execute:
- strict lockfile release closure;
- installed-package upstream migration inspection;
- Laravel architecture/Pest tests;
- PostgreSQL schema snapshot;
- canonical evidence recording;
- artifact provenance / verified source packaging.

These are target gates.

## Closure rule

Stage 16.5.2 closes only after the canonical target reports PASS. Only after canonical evidence has been recorded may the exact verified tree be packaged with `composer release:package`.


## v20.1 corrective — compile derived manifest on the exact target tree

The first v20 target apply failed before quality verification because the installer ran `compile-repository-contracts.php --check` against the manifest packaged in another environment.

That violated the Stage 16.5.2 design: `repository-contract-manifest.json` is a **derived artifact**, not an independent cross-environment authority. The authority sources are the registered contract files; the manifest must be compiled from those sources after they are applied to the exact target tree.

v20.1 adds atomic `--refresh-check` behavior:

```text
resolve exact target authorities
→ write generated manifest
→ instantiate resolver again
→ recompile expected manifest
→ compare exact fingerprint/content
→ continue only if current
```

The installer therefore refreshes the manifest on target before executable-authority verification. Regression `REG-017 derived-artifact-cross-environment-drift` permanently records this class.


## v20.2 corrective — executable authority static type closure

The v20.1 target run reached PHPStan and found three type-contract issues in the newly introduced compiler/doctor layer:

- `SongChartDoctorCommand` null-coalesced a required `authority` key from the documented shaped array;
- `RepositoryContractResolver::impactedAuthorities()` declared `array` without a value type;
- `RepositoryContractResolver::expand()` declared `array` without a value type.

v20.2 keeps runtime behavior unchanged and tightens the static contracts:
- the shaped-array key is accessed directly;
- both iterable parameters are declared as `list<string>` via PHPDoc.

Regression `REG-018 static-analysis-contract-gap` records this class. No PHPStan baseline or analysis level is weakened.


## v20.3 corrective — semantic upstream migration parsing

The v20.2 canonical run reached the installed-package upstream gate. Its failure was in the verifier, not in PostgreSQL or the installed package:

- Spatie v5 declares subject/causer using Laravel `nullableMorphs(...)`; literal-argument parsing therefore missed the generated `*_type` columns.
- Spatie v5's official activity-log migration does not declare `batch_uuid`; SongChart's `batch_uuid` is an intentional SongChart-only audit-correlation extension.

v20.3 introduces `LaravelMigrationColumnExtractor`, which semantically expands the Laravel Blueprint helpers used by the upstream migration:
- `nullableMorphs('subject', ...)` → `subject_type`, `subject_id`;
- `nullableMorphs('causer', ...)` → `causer_type`, `causer_id`;
- `timestamps()` → `created_at`, `updated_at`;
- direct named Blueprint columns remain extracted normally.

`package-schema-contracts.json` now distinguishes:
- `upstream_contract.required_columns`;
- `allowed_adaptations` for SongChart ULID IDs;
- `songchart_extensions`, including `batch_uuid`.

Regression `REG-019 upstream-migration-helper-semantics` permanently records this failure class.

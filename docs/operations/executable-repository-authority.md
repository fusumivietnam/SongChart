# Executable Repository Authority & Contract Compiler

## Purpose

Repository rules must not be copied into Composer scripts, verifiers, tests and installers as independent literals. Stage 16.5.2 resolves each authority once and exposes its fingerprint and consumers through one executable graph.

## Primary authority

```text
docs/project/engineering/repository-contract-compiler.json
```

Each authority declares:
- one source file;
- registered consumers;
- optional forbidden raw literals and allowlists.

## Resolver

```text
App\Support\Engineering\RepositoryContractResolver
```

The resolver can:
- read an authority;
- resolve nested semantic values;
- produce deterministic authority fingerprints;
- compile the complete graph manifest;
- detect forbidden raw literals;
- resolve impacted authorities from changed paths;
- fingerprint the exact source tree and dependency locks.

## Commands

Compile/update the deterministic graph:

```bash
composer repository-compiler:compile
```

Verify it is current and consumers are semantically aligned:

```bash
composer repository-compiler:verify
```

Resolve change impact before implementation:

```bash
php scripts/resolve-repository-impact.php composer.json
```

Explain one authority:

```bash
php artisan songchart:doctor --contract=database-test
```

## Runtime snapshots

Canonical release verification generates:
- `storage/framework/postgres-schema-snapshot.json`
- `storage/framework/package-upstream-adaptations.json`

These are evidence artifacts, not hand-maintained authorities.

## Canonical-first packaging

Canonical evidence records:
- authority graph fingerprint;
- exact source-tree fingerprint;
- `composer.lock` hash;
- `package-lock.json` hash;
- optional Git commit/tree information;
- PostgreSQL/package snapshot hashes.

Packaging is allowed only after canonical closure:

```bash
composer artifact-provenance:verify
composer release:package
```

If source, authority graph or either lockfile changes after canonical verification, provenance verification fails and packaging is refused.


## Derived manifest lifecycle

`docs/project/generated/repository-contract-manifest.json` is generated evidence of the current authority graph. It is not a cross-environment source of truth.

After authority files are applied or changed, use:

```bash
php scripts/compile-repository-contracts.php --refresh-check
```

This writes the manifest from the exact current tree and immediately re-resolves the graph to prove the generated artifact is current. Installers must refresh before checking; a manifest packaged on another machine must never be treated as authoritative for the target.

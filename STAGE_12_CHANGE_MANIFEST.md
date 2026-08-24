# Stage 12 Change Manifest

Status: historical manifest; frozen at Stage 16.4.4.

This file began as the Stage 12 delivery manifest and later accumulated legacy cross-stage notes. It is retained unchanged for traceability but must not receive new stage entries. Chronological repository history from Stage 16.1 onward is owned by `docs/project/DEVELOPMENT_HISTORY.md`.

## Added

- Canonical catalog enums, models and migrations.
- Metadata provenance, conflicts and external identifier schema.
- Provider-to-canonical entity match schema.
- Catalog factories and deterministic fixture seeder.
- Catalog structural verifier and regression tests.
- Stage 12 task contract, model authority and validation report.

## Modified

- `AGENTS.md`
- `README.md`
- `composer.json`
- `database/seeders/DatabaseSeeder.php`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/docs/ROADMAP.md`

## Removed

None.

## Operational impact

Run migrations and seeders before Stage 13. No live provider import is enabled by this stage.

## Stage 12.0.1 correction

- Corrected catalog constraint regression tests to assert `QueryException` explicitly.
- Updated the Stage 12 validation report; no schema or runtime behavior changed.


## Stage 12.1 closure hardening

- Added active provider-match cardinality guard.
- Added normalized metadata-conflict pair guard.
- Expanded catalog verifier and Feature coverage.
- Added Stage 12.1 task contract and closure report.


## Stage 12.3 — Architecture Conformance & Production Boundary Cleanup

Added the canonical stack authority directory, machine-readable stack manifest, executable stack verifier and Architecture guardrails. Updated AGENTS, START_HERE, documentation index, roadmap, README and Composer quality chain. No dependencies, schema or runtime capabilities were added.

## Stage 12.3 — Architecture Conformance & Production Boundary Cleanup

- Added production Eloquent search catalog and explicit local/testing demo binding.
- Centralized catalog entity routing and provider sync state enums.
- Added CatalogEntityResolver for polymorphic boundaries.
- Removed redundant web middleware, dead UI preview links and npm install setup drift.
- Added architecture conformance verifier and regression test.

## Stage 12.4

- Added PostgreSQL-first database authority, explicit SQLite/PostgreSQL test runner, release matrix verifier and architecture guardrail.
- Removed automatic SQLite database creation from project setup.
- Updated CI, testing authority, roadmap and documentation navigation.


## Stage 13.1 — Provider Catalog Contracts

Provider-neutral catalog adapter contracts, immutable ingestion DTOs, tagged registry, failure/rate-limit semantics and executable architecture guardrails were added. Live provider HTTP and persistence remain disabled until later stages.

## Stage 13.3 — Import Job Orchestration, Checkpoint Resume & Failure Recovery

Added append-oriented provider import runs, request audit, immutable raw payloads, processing items, structured failures and resumable checkpoints. Added redaction, dual-database tests and an executable ledger verifier. No live provider HTTP or canonical mutation is enabled.

## Stage 14.1 — Normalized Provider DTOs & Field Presence Semantics
- Added explicit missing/unknown/explicit-null/provided semantics.
- Replaced free-form normalized attributes with typed artist/work/recording/release DTOs.
- Added typed identifiers, relationships, verifier, tests, and authority docs.


## Stage 14.2 — Normalization Validation, Failure Classification & Quarantine

Typed normalized entities are validated before matching or canonical mutation. Invalid items are quarantined with structured validation failures and explicit retry semantics.

## Stage 14.3
Added canonical mutation contracts, transactional upserts, provenance assertions, identifier/relationship attachment and typed mutation outcomes.


## Stage 15.1 — Exact Identity Resolution

Deterministic provider identity matching and audited conflict blocking are now implemented. See `docs/providers/EXACT_IDENTITY_RESOLUTION.md`.

## Stage 15.2 — Conflict Review Foundation

Audited deterministic identity conflict review is implemented.

## Stage 15.3 — Authentication & Privileged Operations Hardening

- Removed default administrator credentials from the seeder and README.
- Added interactive `admin:create` command with hidden password input and Laravel password rules.
- Added active-user and confirmed-2FA middleware boundaries.
- Added native `password.confirm` protection for extension mutations.
- Restricted privileged user attributes from mass assignment.
- Added focused authentication and architecture tests plus an executable verifier.

## Stage 15.4 — Official-First Engineering Governance

Added the canonical official-source policy, reusable task-contract template, executable Composer verifier, architecture regression tests, and pointer-only secondary AGENTS document.

## Stage 15.5 — AI Development Workflow & Change-Surface Governance

Added workflow authority, implementation/debug/validation templates, bounded-context skills, impact-test mapping, change-surface/no-placeholder/workflow verifiers and architecture protection.


## Stage 16.2 — Admin Information Architecture
Read-only operations navigation for catalog, providers, imports, quarantine, identity conflicts, users, extensions, and system health.

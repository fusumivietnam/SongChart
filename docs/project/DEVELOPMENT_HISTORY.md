# Development History

Status: authoritative chronological repository history from Stage 16.1 onward.

## Purpose

Keep stage and hotfix chronology separate from the current-stage pointer. `README.md` is the only authority that declares the current stage. Historical Stage 11 and Stage 12 delivery details remain in their original manifests and foundation records.

## Stage 16 chronology

| Stage | Outcome | Governance evidence |
|---|---|---|
| 17.3.1 | Canonical Artist Admin Editing & Development Status Corrective | Added governed Artist canonical editing with `manage-catalog`, password confirmation and privileged audit; fixed enum-backed latest import rendering so `/development/status` no longer reports false database outages. |
| 16.7.12 | Local Data Safety & Development Auth Ergonomics | Isolates destructive PostgreSQL tests to `songchart_test`, adds a database-role marker and fail-closed safety guard, supports local-only 2FA bypass, and preserves existing local administrator credentials/2FA state. |
| 16.7.11 | PostgreSQL Query Plan & Index Baseline | Adds an evidence-first PostgreSQL query-plan registry, index inventory, audit command, and static guardrails; no speculative index migration is added. |
| 16.1 | Local Development Bootstrap & Demo Readiness | `docs/foundation/STAGE_16_1_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_1_VALIDATION_REPORT.md` |
| 16.1.1 | Windows Storage Junction compatibility hotfix | Recorded as corrective history; no new product capability |
| 16.2 | Admin Information Architecture | `docs/foundation/STAGE_16_2_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_2_VALIDATION_REPORT.md` |
| 16.3 | Catalog Administration | `docs/foundation/STAGE_16_3_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_3_VALIDATION_REPORT.md` |
| 16.3.1 | Catalog ULID route compatibility hotfix | Corrected case-insensitive ULID route acceptance |
| 16.4 | Domain Contract Registry & Schema Authority | `docs/foundation/STAGE_16_4_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_4_VALIDATION_REPORT.md` |
| 16.4.1 | Documentation delivery hotfix | Prevented changeset README from overwriting project README |
| 16.4.2 | Official-source task-contract hotfix | Restored mandatory official-source evidence sections |
| 16.4.3 | Larastan type-contract hotfix | Corrected admin/catalog static-analysis type contracts |
| 16.4.4 | Repository Reconciliation & Historical Closure | `docs/foundation/STAGE_16_4_4_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_4_4_VALIDATION_REPORT.md` |
| 16.5 | Executable Contract Verification & Type Guardrails | `docs/foundation/STAGE_16_5_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_5_VALIDATION_REPORT.md` |
| 16.5.1 | Larastan List-Type Precision Hotfix | `docs/foundation/STAGE_16_5_1_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_5_1_VALIDATION_REPORT.md` |
| 16.5.2 | Dynamic Repository-State Architecture Guardrail Hotfix | `docs/foundation/STAGE_16_5_2_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_5_2_VALIDATION_REPORT.md` |
| 16.5.3 (legacy) | Contract Coverage & Release Baseline Closure | Historical pre-roadmap records preserved as `STAGE_16_5_3_LEGACY_CONTRACT_COVERAGE_*`. |
| 16.5.4 (legacy) | Delivery Verification Authority Hotfix | Historical pre-roadmap records preserved as `STAGE_16_5_4_LEGACY_DELIVERY_VERIFICATION_*`. |
| 16.6 | Provider Operations Console | `docs/foundation/STAGE_16_6_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_6_VALIDATION_REPORT.md` |
| 16.7 | Identity Conflict Review UI | `docs/foundation/STAGE_16_7_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_7_VALIDATION_REPORT.md` |
| 16.7.1 | Baseline Preflight & Provider Operations Repair | `docs/foundation/STAGE_16_7_1_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_7_1_VALIDATION_REPORT.md` |

| 16.7.3 | Target Pint Auto-Repair Hotfix | Target formatter authority correction |
| 16.7.4 | Backed Enum Read-Model Normalization Hotfix | Enum/scalar presenter boundary correction |
| 16.7.5 | Target Pint Auto-Repair for Enum Hotfix | Target formatter authority correction |
| 16.7.6 | Repository History Row Conformance Hotfix | Repository-state metadata correction |
| 16.7.7 | Identity Conflict UI Assertion Contract Hotfix | Acceptance assertion reconciled to task contract |
| 16.7.8 | Repository-Wide Pint Closure Hotfix | Repository style debt closure |
| 16.7.9 | Larastan Identity Read-Model Type Precision Hotfix | Read-model scalar/type precision correction |
| 16.7.10 | Performance, Query & Runtime Reliability Baseline | `docs/foundation/STAGE_16_7_10_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_7_10_VALIDATION_REPORT.md` |
| 16.7.13 | Repository, Schema & Runtime Authority Closure | `docs/foundation/STAGE_16_7_13_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_7_13_VALIDATION_REPORT.md` |

| 16.8.1 | Admin Operations UX & Information Architecture | Reframes administrator surfaces around attention, data sources, data jobs, review work and human-readable operational actions while preserving technical diagnostics as secondary detail. |

| 16.8.2 | Test Authority & Code Generation Guardrails | Makes PostgreSQL the only release-authoritative DB test lane and machine-enforces recurring Pest/Larastan/Eloquent/UI assertion guardrails. |

| 16.0 | Discovery Domain Contracts | Establishes typed, provider-neutral discovery channel/rule/placement/projection contracts over canonical SongChart entities without public rule execution or canonical mutation. |
| 16.1 | Unified Entity Rule Engine | Implements fail-closed typed rule evaluation, canonical field registry, cross-entity snapshot mapping and deterministic sorting without SQL/provider coupling. |
| 16.2 | Discovery Projection Pipeline | Materializes bounded, provider-neutral manual/derived/hybrid Discovery read models with transactional revisions, queue/backfill tooling and scheduled refresh. |
| 16.3 | Laravel Redis Queue & Horizon Deployment Readiness | Standardizes Redis queue taxonomy and job operations, raises PHP baseline to 8.5, and prepares conditional first-party Horizon supervision for Linux/WSL without breaking native Laragon development. |

> Stage-number note: Discovery infrastructure Stage 16.3 reuses the numeric identifier from the earlier Catalog Administration chronology. The original 16.3 governance records are preserved as `STAGE_16_3_LEGACY_CATALOG_ADMINISTRATION_*`.

> Stage-number note: Discovery Stage 16.2 reuses the numeric identifier from the earlier Admin Information Architecture chronology. The original 16.2 governance records are preserved as `STAGE_16_2_LEGACY_ADMIN_INFORMATION_ARCHITECTURE_*`.

> Stage-number note: Discovery Stage 16.1 reuses the numeric identifier from the earlier Local Development Bootstrap chronology after the Discovery roadmap reset introduced at Stage 16.0. The original 16.1 governance records are preserved as `STAGE_16_1_LEGACY_LOCAL_BOOTSTRAP_*`.

| 16.4 | Laravel Pulse Operational Observability | Adopts first-party Pulse for PostgreSQL/query/job/cache/request operational telemetry behind operations-only authorization, without adding a technical dashboard to business admin. |

> Stage-number note: Discovery infrastructure Stage 16.4 reuses the numeric identifier from the earlier Domain Contract Registry chronology. Original 16.4 governance records are preserved as `STAGE_16_4_LEGACY_DOMAIN_CONTRACT_REGISTRY_*`.

| 16.4.1 | Engineering Toolchain & Package Governance Standardization | Makes package admission, Unit/Feature taxonomy, candidate verification and environment diagnostics executable; also fixes Pulse migration exhaustiveness without weakening Larastan. |

> Stage-number note: Discovery infrastructure Stage 16.4.1 reuses an identifier previously used for the historical Documentation Delivery Hotfix. The historical entry remains unchanged above.

| 16.4.2 | Reproducible Verification Environment & PostgreSQL 18 Authority | Adds a Docker-based closure lane with isolated dependency volumes, exact PostgreSQL-major verification, CI major-18 alignment and candidate evidence recording while retaining Laragon development. |

> Stage-number note: Discovery infrastructure Stage 16.4.2 reuses an identifier previously used for the historical Official-source Task-contract Hotfix. The historical entry remains unchanged above.


| 16.4.3 | Docker Local Development & Trusted HTTPS Profile | Adds a port-safe Docker browser-development lane using PostgreSQL 18.4, Redis, Caddy 2.11.3 and mkcert without replacing Laragon or canonical verification. |

| 16.5 | Privileged Operations & Audit | Adds explicit Spatie-backed business audit, privileged user commands, mutation audit coverage and an operations-only audit viewer without broad automatic model logging. |

| 16.5.1 | Repository Contract, Runtime & Release Safety Closure | Converts historical model/schema/package/runtime/test/installer failures into permanent machine-enforced gates and clean PostgreSQL release boundaries. |

| 16.5.2 | Executable Repository Authority & Contract Compiler | Resolves repository authorities once, fingerprints consumers/verified trees, detects raw authority drift, and makes release packaging post-canonical. |
| 16.5.3 | Verification Workflow Consolidation & AI Development Protocol | Consolidates quality/stage/canonical verification ownership and moves AI workflow rules into one executable protocol authority. |
| 16.5.4 | Docker-First Development Environment Consolidation | Makes Docker Desktop/WSL2 the primary development/test runtime, introduces one `songchart` CLI, and downgrades Laragon to compatibility-only. |
| 16.5.5 | Verification Surface Reduction | Removes redundant verification aliases and historical release executables while preserving one stage/canonical/package workflow authority. |
| 16.5.6 | Migration Lifecycle & Upgrade Safety | Freezes historical migrations and adds a PostgreSQL previous-schema to current forward-upgrade closure lane. |
| 16.5.7 | Single Verification Authority & Consumer Graph Closure | Routes every verifier and Architecture test to exactly one semantic authority through the repository compiler. |
| 16.6 | Authorization Consolidation | Consolidates static role→capability semantics into one machine authority and Laravel Gate runtime boundary. |
| 16.7 | Application Data Boundary | Defines controller transport boundaries, read-model ownership, write-service ownership and query-budget registration. |
| 16.7.2 | Pint Style Conformance Hotfix | `docs/foundation/STAGE_16_7_2_TASK_CONTRACT.md`, `docs/foundation/STAGE_16_7_2_VALIDATION_REPORT.md` |
| 16.8 | Provider Mutation & Recovery Controls | Adds authorized, idempotent provider lifecycle and import recovery mutations with row locking and immutable audit evidence. |

> Stage-number note: current-roadmap Stage 16.5.3 reuses an identifier from the earlier Contract Coverage & Release Baseline Closure stage. Historical records are preserved under `STAGE_16_5_3_LEGACY_CONTRACT_COVERAGE_*`.


## Product Integration Era

| Stage | Delivery | Evidence |
|---|---|---|
| 17.10.2 | Development Map + Route Authority | Adds a repository-derived development map and machine route authority, removes obsolete design-preview compatibility aliases, and integrates route ownership into executable repository verification. |
| 17.10.3 | Linux-first CLI + Stable Docker Identity | Makes WSL/Linux the primary host workflow, adds the Bash ./songchart CLI, stabilizes Docker Compose project identity, and fixes host/container UID/GID plus dependency/cache ownership for development. |
| 17.0 | Foundation Closure & Product-State Reconciliation | Reconciles delivered product state, reduces composition/presentation entropy, removes generated runtime cache artifacts, preserves Docker/Windows compatibility entrypoints, and establishes source-hygiene boundaries. Evidence: `docs/foundation/STAGE_17_0_TASK_CONTRACT.md`, `docs/foundation/STAGE_17_0_VALIDATION_REPORT.md`. |
| 17.1.1 | Docker Compose Wrapper Corrective | Fixes PowerShell compose argument forwarding by replacing the reserved/automatic `$Args` helper parameter with explicit `-ComposeArgs`, and hardens Docker-first verification against regression. |
| 17.1.3 | Official-Source Contract & Verification Workflow Corrective | Restores mandatory current-stage official-source evidence and clarifies that stage verification is the iterative subset while canonical verification is the self-contained superset that already executes the stage gate. |
| 17.1.2 | MusicBrainz Test Taxonomy Corrective | Moves the framework-dependent MusicBrainz adapter test from the pure Unit lane to `tests/Feature/Providers` while preserving the strict Unit taxonomy verifier. |
| 17.2 | Artist Import Vertical Slice | Connects MusicBrainz Artist live search/import to the provider ingestion queues, immutable payload ledger, normalization, exact identity, canonical Artist mutation, admin inspection and canonical `/artist/{slug}` public routing; Docker dev now listens to SongChart custom queues and receives MusicBrainz config from project `.env`. |
| 17.2.1 | Redis Cache Connection Runtime Corrective | Restores Docker queue-worker startup by defining Laravel's named Redis `cache` connection, isolating cache on Redis DB 1, and asserting Docker app/queue cache wiring. |
| 17.2.3 | Docker PHP Router Working-Directory Corrective | Runs Laravel's framework development router from `/workspace/public` while preserving service `working_dir=/workspace`, so the router resolves `public/index.php` correctly without breaking Composer/Artisan setup commands. |
| 17.3 | Provider Import Recovery & Operational Hardening | Classifies provider request failures as terminal or retryable, persists retry context, schedules bounded Laravel queue retry, and improves admin import recovery visibility without expanding entity breadth. |
| 17.2.2 | Docker HTTP Environment Propagation Corrective | Replaces Docker dev `php artisan serve` with the direct PHP built-in server/router command so the long-running HTTP process inherits Docker DB/Redis environment exactly; adds contract coverage preventing ServeCommand regression. |
| 17.1 | First Live Provider Contract | Adds MusicBrainz as the first live catalog adapter with fail-closed configuration, one-request-per-second governance, Artist lookup/search normalization, and local provider/pipeline control-center visibility. Evidence: `docs/foundation/STAGE_17_1_TASK_CONTRACT.md`, `docs/foundation/STAGE_17_1_VALIDATION_REPORT.md`. |

| 17.3.2 | Admin Provider Import Workbench & Public Catalog Indexes | Moves MusicBrainz Artist search/import into the governed Admin provider surface and makes `/artists`, `/releases`, and `/collections` canonical public browse paths instead of 404 navigation targets. |
| 17.3.3 | Provider Rate Policy & Global Request Gate | Replaces provider-specific request timing with a provider-neutral operation-aware policy/gate, shares Redis-backed MusicBrainz rate/cooldown state across HTTP and queue processes, and exposes provider rate state in Admin/local control surfaces. |
| 17.4 | MusicBrainz Release Group + Release Vertical Slice | Adds distinct canonical Release Group storage, MusicBrainz Release Group/Release lookup/search normalization, governed Admin import workbench controls, Release→Group linking, partial-date safety and public Release routing. |

| 17.5 | MusicBrainz Recording + ISRC Vertical Slice | Adds MusicBrainz Recording lookup/search/import, ISRC identities, ordered known-Artist credit links, canonical/public Recording surfaces and the identity boundary required before YouTube matching. |
| 17.6 | Essential MusicBrainz Relationships | Adds MusicBrainz group membership, Recording→Work/ISWC identity, Artist Credit join phrases, aliases/selected URLs, Work import, `/works`, and canonical relationship rendering before YouTube admission. |
| 17.6.1 | Admin Catalog Data Boundary Corrective | Restores controller/read-model boundaries for canonical Artist editing by moving persistence and privileged audit into a dedicated write service. |
| 17.6.2 | PHPStan Type Contract Corrective | Restores static-analysis closure by making enum-backed Eloquent values, catalog branching, provider rate strategy flow and MusicBrainz relationship iterable types explicit without suppressions. |
| 17.6.3 | PHPStan Exhaustive Map Corrective | Removes the impossible null-coalescing fallback from the validated exhaustive public-catalog title-column map, preserving runtime behavior while closing the final PHPStan error. |

| 17.6.4 | Canonical Route & Relationship Runtime Corrective | Decouples canonical artist/release/recording aliases from demo search bindings, fixes enum-cast external-identifier comparisons during Recording relationship mutation, and stabilizes the development MusicBrainz workbench text contract. |

| 17.9.4 | Candidate Stage Consistency Closure | Makes candidate verification fail closed when `candidate-verification.json` names a different stage than the README current-stage authority, preventing canonical evidence from being attached to stale stage metadata. |
| 17.10 | Enrichment Orchestrator | In-development candidate now includes deterministic scheduling, database-enforced idempotency, queued request/rate gating, durable execution outcomes, governed MusicBrainz execution through the existing adapter/normalizer, freshness short-circuiting, and daily execution-budget admission; canonical mutation remains out of scope until later slices. |
| 17.12 | Provider Admission Integration | Connects admissible provider field evidence to idempotent metadata assertions and the governed canonical-admission review queue without automatic canonical mutation; also hardens Linux-first candidate/dev-ready workflows and development runtime ownership. Evidence: `docs/foundation/STAGE_17_12_TASK_CONTRACT.md`, `docs/foundation/STAGE_17_12_VALIDATION_REPORT.md`. |

| 18.1 | Rich Entity & Multi-Provider Evidence Model | Extends provider normalization with rich evidence for identifiers, relationships, media, destinations, availability, classifications and metrics; adds provider-specific mapping plus a read-only Admin import preview while preserving validation, identity resolution and governed canonical-admission boundaries. Evidence: `docs/foundation/STAGE_18_1_TASK_CONTRACT.md`, `docs/foundation/STAGE_18_1_VALIDATION_REPORT.md`. |

## History rules

- Add one chronological row when a stage or corrective hotfix is delivered.
- Do not copy the README current-stage marker into this file.
- Do not append new stages to `STAGE_11_CHANGE_MANIFEST.md` or `STAGE_12_CHANGE_MANIFEST.md`.
- Task contracts describe intended scope before implementation; validation reports record only verification actually performed.
- Product roadmap intent remains in `docs/project/docs/ROADMAP.md`; this file records delivered repository history only.

| 17.1.3 candidate v3 | Delivery integrity corrective: post-apply governance evidence checks before Docker canonical verification. |

| 17.1.3 candidate v4 | Governance-record corrective: restores the required current-stage validation report and hardens delivery integrity checks for the task-contract/report pair. |

| 17.7 | YouTube Provider Foundation + Verification Stack Isolation | Introduces Recording-first YouTube candidate discovery/verification/approval, provider destination persistence, public approved/fresh destination exposure, and a dedicated `songchart-verify` Compose project namespace so canonical cleanup cannot stop the long-lived dev stack. |
| 17.7.1 | YouTube Destination Type Contract Corrective | Keeps Stage 17.7 behavior unchanged while making YouTube candidate/pivot/provider-destination projections explicit to PHPStan/Larastan; no suppression and no weakened static-analysis policy. |
| 17.7.2 | Verification Isolation Test Literal Corrective | Fixes the architecture test itself so `$ProjectName` is asserted as a literal PowerShell variable instead of being interpolated as an undefined PHP variable; runtime isolation remains unchanged. |
| 17.8 | Canonical Data Fusion Foundation + Public URL Canonicalization | Reuses provenance assertions/conflicts as a provider-neutral evidence layer, adds deterministic authority/confidence/freshness field resolution and Entity Passport, and replaces generic/singular public entity URLs with plural type-specific canonical paths without redirects while the catalog remains local/unindexed. |
| 17.8.1 | Public Taxonomy & Detail Runtime Corrective | Fixes Stage 17.8 detail/demo runtime regressions, makes entity rows URL-safe, and maps canonical Artist subtypes to `/artists/*` for people and `/groups/*` for group/orchestra/choir without changing canonical Artist identities. |
| 17.9 | Identity Bridge & Enrichment Planner | Unifies provider identifiers/destinations around canonical entities and generates deterministic read-only enrichment plans from missing evidence, provider availability, priority and cost class without calling provider APIs. |
| 17.9.1 | Enrichment Planner Type Contract Corrective | Makes enrichment recipe/provider iterable value types explicit for PHPStan without changing planner behavior, provider recipes, persistence, or runtime orchestration. |
| 17.9.2 | Repository History Closure Corrective | Restores repository-state closure by recording the delivered 17.9.1 corrective in development history and advancing the current-stage governance pointer without changing application runtime. |
| 17.9.3 | Official-Source Governance Closure | Restores the required official-source evidence sections for the Stage 17.9.2 corrective and advances current-stage governance metadata without changing application runtime. |
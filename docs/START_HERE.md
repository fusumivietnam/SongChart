# Start Here

Read in order:

1. `/AGENTS.md`
2. `/docs/START_HERE.md`
3. `/docs/DOCUMENTATION_GOVERNANCE.md` and `/docs/DOCUMENTATION_INDEX.md`
4. The relevant module README under `/docs/providers/`, `/docs/operations/`, `/docs/extensions/` or `/docs/ui/`
5. The phase/status document for the feature being changed
6. The applicable design contract for UI work

Before every feature, create a short task contract:
- goal;
- non-goals;
- acceptance criteria;
- affected module;
- data/provider/policy impact;
- tests.
For plugin/theme work, read `docs/extensions/EXTENSION_LIFECYCLE.md` and linked documents before coding.


## UI work

1. Read `docs/ui/DESIGN_AUTHORITY.md`.
2. For public pages, read `docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`.
3. For admin pages, read `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`.
4. Reuse approved tokens/components.
5. Validate desktop, mobile, loading, empty and error states.


## Search work

Read `docs/ui/PHASE_4_SEARCH_FLOW.md` before changing public search, canonical result rows, entity detail routing or provider chooser behavior.

Read `docs/ui/PHASE_5_HOMEPAGE.md` before changing homepage hierarchy, discovery sections or homepage catalog data.

Read `docs/ui/PHASE_6_SEARCH_RESULTS.md` before changing search facets, result summaries, pagination, result-list semantics or search URL state.

Read `docs/ui/PHASE_7_ENTITY_DETAIL_SYSTEM.md` before changing entity detail identity, facts, relationships, identifiers, provenance or provider presentation.


## Shared test guidance

Before changing Feature tests, read `docs/setup/FEATURE_TEST_ASSERTIONS.md`.

Read `docs/ui/PHASE_8_PROVIDER_CHOOSER.md` before changing provider payloads, availability states, outbound actions, destination policy or disclosure.


## Authentication and account work

Read `docs/ui/PHASE_9_AUTHENTICATION_ACCOUNT_SHELL.md`, `docs/operations/AUTH_2FA_OTP.md` and `docs/project/docs/SECURITY.md` before changing Fortify actions, authentication views, login eligibility, redirects, roles, verification, 2FA or account routes.

## Controller work

Before adding or changing HTTP controllers, verify `app/Http/Controllers/Controller.php` exists and run `ControllerFoundationTest` plus the route-level Feature tests for the affected module.


## Fortify route and optional auth attributes

Before changing verification redirects or 2FA state helpers, confirm framework route names with `route:list` and read the Stage 09 patch safeguards. Do not hard-code Fortify URIs. Treat absent optional 2FA attributes on partial models as an unconfigured state, not as an exception to hide globally.

## Admin dashboard work

Read `docs/ui/PHASE_10_ADMIN_DASHBOARD.md`, `docs/ui/STAGE_10_TASK_CONTRACT.md` and `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md` before changing dashboard metrics, work queues, provider health, sync activity, admin navigation or topbar behavior.

## Foundation alignment work

Before changing framework boundaries, admin authorization, request validation or project verification, read `docs/ui/STAGE_11_TASK_CONTRACT.md`, `docs/foundation/STAGE_11_SOURCE_AUDIT.md` and `docs/project/docs/adr/ADR-003-laravel-native-application-boundaries.md`. Prefer Laravel-native boundaries and do not create a parallel infrastructure layer.

## Documentation governance work

Before creating, moving, renaming or deleting documentation, read `docs/DOCUMENTATION_GOVERNANCE.md`, use `docs/DOCUMENTATION_INDEX.md` to find the current owner, and read `docs/foundation/STAGE_11_2_TASK_CONTRACT.md` for Stage 11.2 changes. Run `composer docs:verify` after documentation edits.
## Laravel feature alignment work

Before adding or replacing authentication, authorization, validation, queue, event, scheduler, cache, rate limiting, notification, HTTP client, filesystem, encryption or logging infrastructure, read `docs/foundation/STAGE_11_3_TASK_CONTRACT.md`, `docs/foundation/STAGE_11_3_LARAVEL_FEATURE_ALIGNMENT_AUDIT.md` and ADR-003. Run `composer alignment:verify` after changing framework boundaries.

## Quality gate compatibility work

Before changing Pint, PHPStan/Larastan, Composer verification or release-package contents, read `docs/foundation/STAGE_11_3_1_TASK_CONTRACT.md` and `docs/foundation/STAGE_11_3_1_QUALITY_GATE_BASELINE.md`. Run `composer quality:normalize`, then `composer quality:verify`.

- Stage 11.4 auth normalization: `foundation/STAGE_11_4_TASK_CONTRACT.md` and `foundation/STAGE_11_4_AUTHORIZATION_MODEL.md`.

- Stage 11.4.2 Larastan cast-boundary work: `foundation/STAGE_11_4_2_TASK_CONTRACT.md`.

- Stage 11.5 request/action work: `foundation/STAGE_11_5_TASK_CONTRACT.md` and `foundation/STAGE_11_5_REQUEST_ACTION_MODEL.md`.
- Stage 11.6 async/provider infrastructure: `foundation/STAGE_11_6_TASK_CONTRACT.md` and `foundation/STAGE_11_6_ASYNC_PROVIDER_MODEL.md`.


- Stage 11.7 testing/CI work: `foundation/STAGE_11_7_TASK_CONTRACT.md` and `foundation/STAGE_11_7_TESTING_CI_MODEL.md`.

## Stage 11 reconciled baseline

Before extending foundation behavior after Stage 11, read:

- `docs/foundation/STAGE_11_FOUNDATION_BASELINE.md`;
- `docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md`;
- `docs/foundation/STAGE_11_8_TASK_CONTRACT.md`.
- `docs/foundation/STAGE_11_9_TASK_CONTRACT.md`;
- `docs/foundation/STAGE_11_9_FOUNDATION_CLOSURE_AUDIT.md`.

Treat prior Stage 11 task contracts and the change manifest as historical implementation records, not replacements for current authorities.

## Stage 12 catalog work

Before canonical schema, provenance, provider mapping, fixtures or import preparation, read `docs/catalog/STAGE_12_TASK_CONTRACT.md` and `docs/catalog/STAGE_12_CATALOG_MODEL.md`. Provider IDs must remain external identities.

- `docs/catalog/STAGE_12_1_TASK_CONTRACT.md` — Stage 12 closure scope and acceptance criteria.
- `docs/catalog/STAGE_12_1_CLOSURE_REPORT.md` — hardened invariants and runtime closure evidence.

## Technology stack and dependency work

Before adding packages, framework-adjacent abstractions, provider clients, persistence layers, queue infrastructure or frontend runtimes, read the complete authority set under `docs/project/stack/`, beginning with `STACK_OVERVIEW.md`, `FRAMEWORK_BASELINE.md`, `PACKAGE_REGISTRY.md` and `CAPABILITY_OWNERSHIP.md`. Run `composer stack:verify`.

- `foundation/STAGE_12_3_TASK_CONTRACT.md` — Stage 12.3 architecture cleanup contract.
- `foundation/STAGE_12_3_VALIDATION_REPORT.md` — Stage 12.3 validation evidence.

- `foundation/STAGE_12_4_TASK_CONTRACT.md` — PostgreSQL-first test matrix contract.
- `foundation/STAGE_12_4_VALIDATION_REPORT.md` — Stage 12.4 closure evidence.


## Stage 13.3 — Import Job Orchestration, Checkpoint Resume & Failure Recovery

Provider-neutral catalog adapter contracts, immutable ingestion DTOs, tagged registry, failure/rate-limit semantics and executable architecture guardrails were added. Live provider HTTP and persistence remain disabled until later stages.

- `docs/providers/NORMALIZATION_VALIDATION_AND_QUARANTINE.md` — Stage 14.2 validation and quarantine authority.

- Stage 14.3 canonical writes: `providers/CANONICAL_MUTATION_ACTIONS.md`


## Stage 15.1 — Exact Identity Resolution

Deterministic provider identity matching and audited conflict blocking are now implemented. See `docs/providers/EXACT_IDENTITY_RESOLUTION.md`.


## Repository state and history

`README.md` is the only current-stage pointer. Use `docs/project/DEVELOPMENT_HISTORY.md` for chronological stage and hotfix history. Do not duplicate the README current-stage marker in this reading map or another authority. Run `composer repository-state:verify` after stage/documentation reconciliation changes.


## AI development workflow

Read `docs/project/docs/ENGINEERING_WORKFLOW.md`, the applicable `.agents/skills/*/SKILL.md` routing file, and the current task contract before implementation. Use the plan, debugging and validation templates under `docs/templates/`.


## Stage 16.2 — Admin Information Architecture
Read-only operations navigation for catalog, providers, imports, quarantine, identity conflicts, users, extensions, and system health.

## Domain contract work

Before changing canonical entity fields, migrations, Eloquent fillable/casts, cross-entity search/admin mapping, ULID/slug semantics or catalog routes, read `docs/project/domain/DOMAIN_MODEL.md`, `FIELD_NAMING.md`, `IDENTIFIERS.md`, `RELATIONSHIPS.md`, `URL_CONTRACTS.md`, and the machine authority `domain-contracts.json`. Run `composer domain-contracts:verify`. Do not invent undeclared fields in application code.

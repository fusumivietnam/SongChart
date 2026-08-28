# Stage 18.4 Task Contract — Admin Completion & Operational Convergence

Status: active task contract.

## Goal

Complete the administrator-facing operational surfaces required to run SongChart day to day without depending on Tinker, direct database edits, or routine `.env` changes for supported operations.

## Non-goals

- No new public product information architecture or visual redesign beyond admin-operational needs.
- No new provider breadth unless an existing admin workflow cannot be completed without it.
- No complex OAuth account-linking program, automatic credential rotation platform, or advanced scheduler framework.
- No release-production deployment topology; that remains in 19.x production readiness.
- No weakening existing authorization, audit, verification, PostgreSQL, candidate, or canonical gates.

## Acceptance criteria

- Admin dashboard exposes actionable operational state and routes operators to the owning workflow instead of requiring shell/Tinker diagnosis.
- System Settings covers supported runtime-configurable product/provider settings through governed application boundaries.
- Provider management exposes enablement, operational health, rate/cooldown state, configured credential-pool status, and supported recovery actions without exposing secrets.
- Import operations expose progress, retryability, terminal/retryable failure context, and bounded recovery controls.
- Canonical admission and identity-conflict queues are operable end to end from Admin with authorization and privileged audit evidence.
- Catalog administration supports the current canonical entity set without bypassing application write/read boundaries.
- User/role operations use the existing Laravel Gate authorization authority and preserve last-super-admin/business invariants.
- Privileged audit is discoverable and useful for tracing administrator mutations.
- Existing operational commands may remain for engineering/recovery, but normal supported administrator workflows must not require Tinker or direct `.env` editing.
- AI/dev status is visible in `docs/project/DEVELOPMENT_STATE.md` as Done / In progress / Next with a compact stage graph.
- Reusable use-case/debug/failure learnings are evidence-driven; a durable rule requires promotion into its real authority plus a permanent machine guard.
- All new/changed admin operations have focused regression coverage, impact routing, generated-authority reconciliation where required, and exact-tree candidate/canonical closure.

## Affected modules and boundaries

- Admin dashboard and navigation.
- System Settings application/read/write surfaces.
- Provider operations, credential-pool status and provider health presentation.
- Import jobs/recovery and operational progress presentation.
- Canonical admission review.
- Identity conflict review.
- Catalog administration.
- User/role administration.
- Privileged audit viewer.
- Authorization, application-data-boundary, query-budget and audit authorities where applicable.
- AI/dev operational state and evidence-learning documentation.

## Expected files

- `app/Http/Controllers/Admin/**`
- `app/Application/**` Admin read models/actions touched by accepted slices
- `app/Support/**` operational services where already authoritative
- `resources/views/admin/**`
- `routes/**` only where a governed Admin route is missing
- `docs/project/security/authorization-contract.json` only when capability ownership changes
- `docs/project/domain/application-data-boundary.json` when new read/write surfaces are registered
- `docs/project/performance/query-budget-contract.json` when new operational read models require budgets
- applicable provider/operational machine authorities
- `tests/Feature/Admin/**` and existing Admin feature suites touched by accepted slices
- `tests/Architecture/**` for durable boundaries only
- `docs/project/DEVELOPMENT_STATE.md`
- `docs/project/engineering/AI_LEARNING_LEDGER.md`
- `docs/foundation/STAGE_18_4_VALIDATION_REPORT.md`
- `docs/project/generated/**` after reconcile when registered inputs change

## Planned impact

- Planned changed paths: Admin controllers/read models/actions/views, focused Admin tests, and only the authorities owning those surfaces.
- Expected semantic authorities: authorization, application-data-boundary, provider/runtime operational contracts, audit, verification command/consumer routing as impacted.
- Expected reverse verification consumers: Admin architecture tests, authorization/data-boundary verifiers, provider operational tests, impacted feature tests.
- Expected focused checks: `./songchart impact`, relevant contract verifiers, focused Pest, Pint, PHPStan, then actual-diff impact.
- Record `./songchart impact <planned-paths...>` evidence before each implementation slice.

## Allowed incidental files

- `docs/project/generated/**` generated authority outputs.
- formatter-only changes required by locked Pint.
- candidate/canonical runtime evidence under ignored runtime storage.
- lockfiles only when an explicitly accepted dependency change is required; no dependency is planned at initialization.

## Post-diff impact and scope deviations

- Run `./songchart impact --diff` after each coherent slice and before closure.
- Record newly impacted authorities/consumers/checks not present in planned impact.
- Record every changed file outside the planned surface with reason and verification.
- Use `None` only after actual diff confirms no deviations.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/engineering/AI_LEARNING_LEDGER.md` as evidence/history only, never as a parallel rule authority
- `docs/project/security/authorization-contract.json`
- `docs/project/domain/application-data-boundary.json`
- `docs/project/performance/query-budget-contract.json`
- applicable provider/runtime/operational contracts discovered by impact resolution
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`

### Installed versions

Use `composer.lock`, `package-lock.json` and `./songchart context --json` as version/runtime authorities. Do not infer versions from memory.

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel 13 | https://laravel.com/docs/13.x/authorization | Gates/policies and authorization of administrator actions | 2026-08-28 |
| Laravel 13 | https://laravel.com/docs/13.x/validation | Request/Form Request validation and authorization boundaries | 2026-08-28 |
| Laravel 13 | https://laravel.com/docs/13.x/queues | failed jobs, bounded retries and queue recovery primitives | 2026-08-28 |
| Laravel 13 | https://laravel.com/docs/13.x/configuration | configuration/environment ownership and config-cache semantics | 2026-08-28 |
| Laravel 13 | https://laravel.com/docs/13.x/http-client | bounded upstream HTTP retry primitives where provider adapters need them | 2026-08-28 |

Official documentation establishes Laravel behavior only. SongChart-specific role capability matrices, provider retry classes, canonical admission semantics, credential ownership and audit requirements remain repository-owned contracts.

### Native capability assessment

- Capability owner: Laravel application boundaries + existing SongChart Admin/read-model/action/provider/audit infrastructure.
- Native/first-party capability available: mostly yes/partial.
- Selected primitives: existing Gates, middleware/password confirmation, validation, queues/failed-job primitives, controllers-as-transport, read models, actions/services, Blade, configuration and current provider/audit infrastructure.
- Why they satisfy the requirement: Stage 18.4 is convergence/completion, not a new admin framework.

### Custom implementation justification

- Custom code required: yes, bounded to missing product-specific Admin workflows.
- Missing official behavior: SongChart-specific canonical admission, identity resolution, provider recovery, catalog and operational semantics.
- Narrow custom boundary: product operations and presentation only.
- Framework primitives reused: Laravel Gates, validation, queues, Eloquent/Query Builder inside registered read/write boundaries, Blade and existing audit/provider infrastructure.
- Non-goals: custom admin framework, custom authorization system, custom queue/scheduler framework.

## Domain contract and use-case data surface

For every accepted Admin slice, record:

- actor and capability;
- exact read model / fields read;
- exact action/service / fields mutated;
- null/unknown/failure semantics;
- route and presentation contract;
- relationship invariants;
- relevant domain/operational contract changes.

## Security, authorization, and data impact

- All privileged operations authorize through Laravel Gates and existing capability authority.
- No secret credential material may be rendered in Admin; expose status/identity metadata only where permitted.
- Privileged mutations require existing audit semantics or an explicit extension of the audit authority.
- Controllers remain transport adapters; direct persistence/query-builder violations are prohibited.
- Deployment-owned secrets and environment-only configuration must not become database-editable merely for UI convenience.

## Verification plan

- Preflight authority consistency before feature work.
- Planned impact before each coherent implementation slice.
- Actual-diff impact after implementation.
- `./songchart reconcile` when registered generated-authority inputs change.
- `./songchart audit` for collect-all feedback before closure.
- Focused implementation gates: impacted contract verifiers + Pint/PHPStan + focused Admin Feature/Architecture tests.
- Stage closure owner: `./songchart candidate` / `composer stage:verify`.
- Canonical closure owner: `./songchart close` / `composer canonical:verify`.
- Post-closure seal: any tracked change invalidates closure evidence and requires reclosure.

## Tests and verification

- Feature tests for every operator-visible workflow and authorization boundary.
- Architecture tests only for durable transport/read/write/authority invariants.
- PostgreSQL-backed tests for persistence/recovery workflows.
- No SQLite compatibility evidence may substitute for PostgreSQL release authority.
- Pint and PHPStan/Larastan remain mandatory for touched PHP.

## Documentation impact

- Keep this contract as scope/acceptance owner.
- Update `docs/project/DEVELOPMENT_STATE.md` as Done / In progress / Next, blocker, evidence and next action change.
- Keep a compact graph in Development State so a new AI/dev can orient without reconstructing chronology.
- Use `AI_LEARNING_LEDGER.md` for evidence/history of reusable failure classes only; promote durable rules into their owning authority.
- Update validation report with executed evidence.
- Keep Roadmap future-looking and Development History chronological.

## Command mutation envelope

- `impact`, `audit`, candidate verification: read-only with respect to tracked source.
- `reconcile`: may change only governed generated authority.
- normal Admin tests/verification: runtime/test storage only.
- unexpected tracked mutation is a workflow defect and must be fixed at the owning command.

## Delivery and handoff

- One writer per overlapping source surface.
- Handoff by exact commit SHA after push.
- Generated JSON is regenerated, not manually merged.
- Final PR must point at the exact canonical-closed HEAD.

## Rollback

- Revert bounded Stage 18.4 commits by slice.
- Schema changes, if later accepted, require forward/rollback analysis under migration lifecycle authority.
- Operational mutations must preserve auditability and must not require destructive manual DB repair for normal rollback.

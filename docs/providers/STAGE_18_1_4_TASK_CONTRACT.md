# Stage 18.1.4 Task Contract — Preview → Governed Import Plan

Status: slice task contract. The canonical current-stage contract remains `docs/foundation/STAGE_18_1_TASK_CONTRACT.md`.

## Goal

Turn a valid read-only provider preview into an explicit, deterministic import plan that an authorized operator can inspect and confirm before SongChart creates an existing governed provider import run.

## Non-goals

- Direct canonical mutation.
- New provider fetch infrastructure or a second import pipeline.
- Bulk multi-provider execution.
- Persisting preview payloads as canonical/provider evidence before the existing ingestion pipeline fetches them.

## Acceptance criteria

- The plan names provider, entity type, external ID and the existing import operation to run.
- The plan reports identifiers, fields, relationships, rich evidence, review items and zero direct canonical mutations.
- A deterministic fingerprint binds provider/entity/external ID/raw payload and normalized preview.
- Execution rebuilds preview and plan and rejects a stale/tampered fingerprint.
- Execution requires `manage-providers` and password confirmation.
- Execution calls `ProviderImportOrchestrator` and redirects to the existing import-run detail page.
- Disabled or missing providers return operator-friendly validation feedback rather than a raw exception.

## Boundaries

Read-only:

`ProviderPayload → ProviderSpecificMapperRegistry → ProviderImportPreviewBuilder → ProviderImportPlanBuilder`

Mutation:

`Admin confirmation → StartGovernedProviderImportPlan → ProviderImportOrchestrator → existing provider queue`

Canonical mutation remains outside this slice and continues to be governed by the existing downstream admission boundaries.

## Security and data impact

- No new schema.
- No new capability.
- No secrets stored or displayed.
- The pasted preview payload remains request-scoped.
- Only the existing import run configuration stores the plan fingerprint and operational source marker.

## Verification

Focused tests:

- `tests/Unit/Providers/ProviderImportPlanBuilderTest.php`
- `tests/Feature/Admin/ProviderImportPreviewTest.php`
- existing rich provider normalization/preview tests
- PHPStan/Larastan
- candidate closure after generated authority refresh when requested

Canonical verification remains required before Stage 18.1 closure.

# Stage 17.12 — Provider Admission Integration

Status: implementation candidate; canonical closure pending.

## Objective

Connect governed provider/enrichment evidence to the canonical-admission queue without allowing provider execution to mutate canonical entities directly. Also tighten the Linux-first developer workflow with command fallback and an explicit candidate-preparation command.

## Scope

- Materialize admissible provider field evidence into `metadata_assertions`.
- Stage those assertions through the existing governed canonical-admission service.
- Preserve identity/identifier evidence outside field mutation admission.
- Keep canonical mutation behind explicit admission review.
- Make `./songchart artisan`, `composer`, `npm`, and `dev shell` work whether the persistent app service is running or not.
- Add `./songchart candidate` for explicit context refresh + whitespace check + candidate verification.
- Keep `./songchart verify` read-only and fail-closed.

## Non-goals

- No direct provider-to-canonical mutation.
- No automatic approval of canonical admission decisions.
- No new provider implementation.
- No verification-gate weakening.
- No destructive development database lifecycle changes.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/START_HERE.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/project/domain/route-authority.json`
- `docs/project/engineering/verification-command-surface.json`

### Installed versions

Stage 17.12 does not introduce dependency upgrades. Runtime and dependency versions remain owned by the repository lockfiles and canonical Docker verification environment.

### Official external sources

No new third-party package, API, or external service is introduced by this stage. Existing provider behavior remains governed by the already-adopted provider contracts and official-source records.

### Native capability assessment

- Existing `EnrichmentEvidenceAdmissionPolicy` remains the evidence decision boundary.
- Existing `MetadataAssertion` remains the provenance/evidence persistence model.
- Existing `GovernedCanonicalAdmissionService` remains the canonical admission staging and mutation boundary.
- Existing Docker Compose `exec` and `run --rm` capabilities are reused for CLI fallback.
- Existing project-context generator remains the only generated-context writer.

### Custom implementation justification

A small application handoff is required because Stage 17.11 supplied a canonical admission queue but the enrichment job still stopped after persisting admissible evidence. `MaterializeProviderAdmissionEvidence` bridges those existing boundaries without duplicating provider normalization, evidence admission, or canonical mutation logic.

The CLI wrapper needs a small running-service probe so host commands can choose native Compose `exec` when the service is running and `run --rm` otherwise. `candidate` is explicit preparation; canonical verification remains non-mutating.

## Tests and verification

Required evidence includes:

- provider field evidence creates one candidate `MetadataAssertion` and one pending canonical admission decision;
- materialization is idempotent;
- canonical entity remains unchanged before explicit admission approval;
- enrichment job records canonical-admission identifiers in result payload;
- materialization failure becomes `review_required` rather than canonical mutation;
- `bash -n songchart`;
- CLI fallback works with app running and stopped;
- `./songchart candidate` refreshes context explicitly and runs candidate verification;
- `./songchart verify` remains read-only;
- `git diff --check`;
- Pint, PHPStan, Pest/PostgreSQL and repository contract gates pass;
- final `./songchart verify` canonical closure passes.

No PHPStan, Pint, Pest, PostgreSQL, repository-contract, schema, route, architecture, or canonical verification gate may be weakened.

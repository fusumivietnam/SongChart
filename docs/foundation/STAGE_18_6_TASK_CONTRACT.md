# Stage 18.6 Task Contract — Provider Destination & Media Quality

Status: active task contract.

## Goal

Make approved provider destinations and media safer and more useful by selecting them deterministically from availability, freshness, provenance and review evidence, while giving operators clear visibility into stale or unavailable destinations.

## Non-goals

- No broad provider-expansion program merely to increase provider count.
- No automatic canonical identity mutation from media/provider destinations.
- No opaque AI/recommendation ranking.
- No provider-specific identifier becoming canonical primary identity.
- No speculative caching, scheduler or schema framework without an accepted use case.
- No direct controller persistence/query composition.

## Acceptance criteria

- Public destination selection is deterministic and provider-neutral over already-approved destination evidence.
- Unavailable, private, non-embeddable or policy-invalid media cannot become a preferred playable destination.
- Freshness/last-checked state participates explicitly in destination eligibility and preference.
- Provenance and review state remain inspectable for the chosen destination.
- Admin operations surface stale/unavailable destination attention with clear Vietnamese operator language and actionable next steps.
- YouTube approval/verification quality preserves privacy-status and embeddability checks and does not equate a YouTube video with canonical Recording identity.
- Provider breadth expands only where an official API capability and evidence-quality use case justify it.
- Existing canonical/public URL, provider credential, rate, admission, audit and authorization authorities remain intact.

## Affected modules and boundaries

- `app/Domain/Providers/Destinations` and existing destination/media DTO/value semantics.
- `app/Support/Providers/Destinations` provider adapters/workbenches and selection logic.
- `app/Application/Admin` / `app/Support/Admin` read models when operator attention is required.
- Public catalog/entity presentation where an approved destination is selected.
- Existing provider destination persistence only when the current schema can express the accepted use case; schema changes require explicit contract/authority update first.
- `docs/providers/`, `docs/project/domain/`, `docs/ui/` and `docs/project/engineering/` only where their owned contracts are actually affected.

## Expected files

Initial inventory only; exact files must be narrowed through planned impact before each implementation slice.

- Existing destination/media domain and support classes.
- Existing Admin provider/destination presentation and focused tests.
- Existing public recording/media projection and focused tests.
- This task contract, `docs/project/DEVELOPMENT_STATE.md`, and validation evidence as the stage progresses.

## Planned impact

- Planned changed paths: destination/media domain, provider adapter/selection surfaces, Admin/public projections, focused tests, owning docs.
- Expected semantic authorities: provider destination/media contracts, application-data boundary, provider compliance/official-source policy, UI operational language.
- Expected reverse verification consumers: architecture/data-boundary tests, provider destination tests, public catalog tests, Admin operations UX checks, repository compiler consumers where authority inputs change.
- Expected focused checks: derive with `./songchart impact <planned-paths...>` before each bounded slice.

## Allowed incidental files

- `docs/project/generated/*` only through governed reconcile after authoritative source/contract changes.
- Formatter-only changes on exact touched PHP files.
- Validation/current-state documentation needed to keep the active checkpoint truthful.

## Source hygiene and verifier ownership before commit

Before committing authoritative source/contract/test changes:

- run Pint in write mode on exact changed PHP paths, inspect diff, then require Pint `--test`;
- register any new verifier/Architecture test under exactly one existing verification-consumer owner;
- run repository compiler/consumer ownership verification before broad quality verification;
- commit authoritative source first, then reconcile generated authority separately.

Required order:

```text
SOURCE / CONTRACT / TEST CHANGE
        ↓
PINT WRITE ON CHANGED PHP
        ↓
PINT --TEST
        ↓
VERIFIER OWNER (when applicable)
        ↓
SOURCE COMMIT
        ↓
./songchart reconcile
        ↓
GENERATED-ONLY COMMIT (when needed)
        ↓
./songchart impact --verify
```

## Post-diff impact and scope deviations

- Run `./songchart impact --diff` after each implementation slice.
- Any new route, schema, provider capability, policy or persistence field must be recorded in the owning authority before use.
- Record changed files outside planned scope with reason and verification; do not normalize accidental scope expansion into the stage.

## Command mutation envelopes

No new workflow command is planned for Stage 18.6. Existing `impact`, `reconcile`, `candidate`, `verify` and `close` semantics remain owned by engineering workflow authority.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/DEVELOPMENT_STATE.md`
- `docs/project/docs/ROADMAP.md`
- `docs/project/domain/`
- `docs/providers/`
- `docs/ui/`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`

### Installed versions

Use repository lockfiles and stack authority; do not change runtime/package baselines as part of destination quality work unless separately justified.

### Official external sources

For every provider behavior touched, review the provider's official API/policy documentation for privacy, embeddability, availability, quota/rate and resource semantics before implementation. Record exact source evidence in the implementation slice when applicable.

### Native capability assessment

- Capability owner: existing Laravel HTTP/cache/queue primitives plus existing SongChart provider destination contracts.
- Native/first-party capability available: partial.
- Selected primitive: reuse existing adapters, request gate, credential boundary, Eloquent persistence, Gates and audit surfaces.
- Why it satisfies the requirement: Stage 18.6 is a policy/selection/quality convergence over existing capabilities, not a new integration framework.

### Custom implementation justification

- Custom code required: yes, narrowly for provider-neutral destination eligibility/preference and quality presentation.
- Missing official behavior: Laravel/provider APIs do not define SongChart's cross-provider preference semantics.
- Narrow custom boundary: deterministic domain/application selection over normalized destination evidence.
- Framework primitives reused: existing Laravel and SongChart provider infrastructure.

## Domain contract and use-case data surface

- Actor and preconditions: public readers consume only eligible approved destinations; authorized provider/catalog operators review/repair destination state.
- Input types and identifier formats: existing canonical entity IDs and provider resource IDs; provider IDs remain external identities only.
- Exact entity fields read: existing destination approval/review, URL/resource, embeddability/privacy, verification/check timestamps, provenance/match evidence and related canonical recording metadata as required by the selected use case.
- Exact entity fields written: only existing destination verification/review fields unless an explicit schema contract is approved.
- Null/unknown semantics: unknown freshness/availability must not be silently treated as fresh/available.
- Output DTO/presentation contract: deterministic selected destination plus explainable eligibility/provenance state.
- Route/API contract: reuse existing routes unless a new accepted use case requires authority update first.
- Relationship invariants: destination evidence may attach to a canonical entity but does not define canonical identity.
- Contract changes required: only when implementation proves an existing contract cannot express the accepted use case.

## Security, authorization, and data impact

- Preserve `manage-providers`/catalog authorization and password confirmation for privileged mutations.
- Do not render provider secrets or move secret ownership into public/admin projections.
- Preserve privileged audit for approval/configuration mutations.
- Provider API policy, privacy and quota semantics are fail-closed inputs to eligibility.

## Verification plan

- Planned impact: `./songchart impact <planned-paths...>`.
- Actual diff: `./songchart impact --diff`.
- Changed-PHP formatter: Pint write exact touched PHP then `--test`.
- Verification-consumer ownership: exactly one owner for new verifier/Architecture tests.
- Generated authority: `./songchart reconcile` after authoritative source commits.
- Pre-closure: `./songchart impact --verify`.
- Collect-all diagnostic: `./songchart audit` where useful.
- Focused implementation gates: destination/media unit/feature tests, Admin operations UX tests, public Recording/entity tests, PHPStan as impacted.
- Stage closure: `./songchart candidate`.
- Canonical closure: `./songchart verify` or governed `./songchart close`.
- Packaging: only after canonical PASS from accepted exact tree.

## Tests and verification

Behavioral coverage must include deterministic selection, stale/unavailable rejection, privacy/embeddability safety, provenance/review-state presentation, and no direct canonical-identity mutation. PostgreSQL remains release-authoritative.

## Post-closure seal

After canonical PASS, record exact closed HEAD, require clean tracked state, push that exact HEAD, and merge only if PR checks target the same SHA. Any tracked change after canonical invalidates closure evidence.

## Documentation impact

- Keep `DEVELOPMENT_STATE.md` current as slices/evidence advance.
- Move completed 18.6 chronology to Development History only after governed acceptance.
- Keep roadmap future-facing; do not use it as a debug diary.

## Delivery and handoff

- Current writer/owner: Stage 18.6 branch.
- Handoff commit SHA: exact pushed SHA at writer/device synchronization points.
- Local-only commits before handoff: none.
- Upstream relationship before handoff: branch must not be known behind/diverged.
- Exact closed HEAD expected before PR merge: required.

## Rollback

Revert bounded destination/media policy/presentation slices independently where possible. Do not roll back accepted canonical/provider identity history or remove audit evidence as part of a destination-quality rollback.

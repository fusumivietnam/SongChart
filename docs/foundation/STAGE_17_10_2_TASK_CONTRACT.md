# Stage 17.10.2 — Development Map + Route Authority

Status: implementation candidate contract.

## Goal

Make repository continuation and HTTP routing self-describing without adding a parallel hand-maintained AI state source, while reducing obsolete development-only route aliases in favor of one canonical design-system route family.

## Scope

- add machine-readable route authority;
- add a repository-derived development map;
- retire duplicate design-system compatibility aliases;
- migrate internal links/tests to canonical development design-system routes;
- add a verifier preventing retired aliases from returning;
- register the verifier in executable repository authority ownership;
- keep historical manifests and authoritative documentation intact;
- preserve generated context/verification state as derived repository evidence rather than hand-maintained chat state.

## Non-goals

- no product behavior changes outside development-only design-system routing;
- no database/schema/provider mutation changes;
- no authentication or authorization model changes;
- no new runtime dependency or package;
- no new hand-maintained session/handoff document;
- no weakening of repository-state, candidate-contract, official-source, repository-compiler, route, or canonical verification gates.

## Acceptance criteria

1. `/development/design-system/*` is the sole design-system HTTP family.
2. `/ui-preview*`, `/shell-preview/*`, and `/design-lab*` compatibility aliases are absent.
3. `docs/project/domain/route-authority.json` declares the canonical route family and retired aliases.
4. `composer route-authority:verify` passes and the verifier has exactly one repository verification owner.
5. `php scripts/development-map.php --write` produces the committed development map from repository authorities.
6. README/current-stage, development state, development history, candidate verification, generated project context, and generated repository manifest remain mutually consistent.
7. Existing repository/candidate/canonical verification remains the closure authority.
8. Historical delivery manifests/evidence are preserved unless a separate authority-backed cleanup explicitly retires them.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/generated/project-context.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/DEVELOPMENT_STATE.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `README.md`
- `candidate-verification.json`
- `composer.json`
- `routes/web.php`

### Installed versions

No dependency or runtime version changes are introduced by this stage. The current repository context remains authoritative, including PHP 8.5, Laravel Framework 13.25.0, Node 22, PostgreSQL major 18, Redis-backed queues, and the lockfile-owned Composer/npm dependency set.

### Official external sources

No new external source is required for this stage. The implementation governs repository-local route declarations, generated repository context, documentation/state consistency, and verification ownership. It does not introduce or depend on a new Laravel feature, third-party package, provider API, browser API, database feature, or external protocol.

### Native capability assessment

- Capability owner: Laravel routing plus SongChart repository authority/verification infrastructure.
- Native/first-party capability available: yes for HTTP route declaration, naming, middleware, and runtime route collection.
- Selected primitives: existing Laravel `Route` declarations and named routes in `routes/web.php`, existing repository machine authorities, Composer script composition, generated repository context, and executable verification ownership.
- Why they satisfy the requirement: Laravel remains the runtime routing implementation, while SongChart-specific canonical/retired-route policy is represented as repository governance rather than duplicated routing infrastructure.
- Rejected alternative: adding a second route registry or a hand-maintained chat/session state document would duplicate runtime/repository truth and increase drift risk.

### Custom implementation justification

- Custom code required: yes, but narrowly scoped to repository-specific governance/projection.
- Missing official behavior: Laravel does not define SongChart's canonical-versus-retired route policy, cross-chat development map, repository current-stage consistency, or verifier ownership graph.
- Narrow custom boundary: `docs/project/domain/route-authority.json`, `scripts/verify-route-authority.php`, `scripts/development-map.php`, generated development-map output, and the existing repository verification graph/Composer entrypoints.
- Framework primitives reused: Laravel routes/named routes, Composer scripts, existing repository compiler/context authorities, and existing test infrastructure.
- Non-goals: replacing Laravel routing, adding custom HTTP dispatch, introducing another verification engine, or creating a parallel source of project truth.

## Security, authorization, and data impact

- No database schema or persisted application data changes.
- No provider credentials, secrets, or external network behavior changes.
- No authorization capability changes.
- Removed routes are development/design-system compatibility aliases only; canonical development routes retain their existing middleware/environment behavior.
- Public catalog/application route semantics are intentionally outside this stage.

## Tests and verification

Focused/static verification:

- `php scripts/verify-route-authority.php`
- `php scripts/development-map.php --write`
- `php scripts/verify-official-sources.php`
- `php scripts/verify-repository-state.php`
- `php scripts/verify-candidate-contract.php`
- `php scripts/verify-repository-contract-compiler.php`
- affected Feature tests for design-system/UI preview routes

Candidate/canonical closure:

- `songchart test` / Docker stage verification during iteration
- `composer stage:verify` inside the supported verification environment
- `songchart verify` / `composer canonical:verify` for final canonical closure

No release/canonical PASS is claimed by this task contract until the exact target tree completes the repository's canonical Docker verification lane.

## Rollback

Restore the previous route declarations/tests/internal links and remove the Stage 17.10.2 route/development-map authority additions from the same changeset. No database rollback is required.

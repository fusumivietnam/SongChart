# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- `main` contains merged Stage `18.1 — Rich Entity & Multi-Provider Evidence Model` and the post-18.1 repository-hygiene corrective.
- Stage 18.1 canonical verification passed before merge; its governed provider-evidence boundaries are the accepted product baseline.
- Development authority is Linux/WSL2 + Docker through the repository `./songchart` CLI. GitHub Codespaces is the preferred remote adapter; native Windows Batch/PowerShell, Laragon execution paths, ZIP handoff and patch-installer workflows are retired.

## Current stage

- Stage `18.2 — Public Search & Canonical Surfaces`
- Candidate: `v1`
- Branch: `stage-18.2-public-search-canonical-surfaces`
- Candidate closure: pending.

## Implemented slices

- Stage 18.2 executable task contract and validation record;
- `candidate-verification.json` initialized for Stage 18.2 instead of reusing Stage 18.1 closure evidence;
- production `EloquentSearchCatalog` relevance semantics moved into the canonical database query: exact title → prefix → substring;
- stable relevance tie-breaks by normalized title, entity type and canonical identity;
- query-wide facet counts preserved even when a selected entity type scopes the displayed page;
- canonical public routes/pages for Artist/Group, Release, Recording, Work and Collection preserved as the public surface baseline;
- focused PostgreSQL-backed coverage added for relevance ordering, global facets and canonical group URLs;
- public search remains behind the existing `SearchCatalog` read boundary with thin HTTP controllers and no provider request path.

## Current blockers / risks

- Focused Stage 18.2 tests and PHPStan have not yet been executed on the new exact tree.
- Cross-entity result collection remains bounded per canonical entity type; if real catalog scale demonstrates that this bound affects recall, a later Stage 18.2 slice should replace it with a dedicated PostgreSQL search projection rather than silently increasing the cap.
- Volatile successful verification evidence can still rewrite tracked `candidate-verification.json`; treat that as a bounded workflow defect rather than a product blocker.
- Shared demo remains optional/unconfigured and does not block product development.

## Latest focused evidence

- Stage 18.1 candidate and canonical verification passed.
- PR #7 merged Stage 18.1 and PR #8 merged the post-18.1 repository hygiene into `main`.
- Existing public SearchFlow/SearchResults/PublicCatalogBrowse coverage was green on the accepted baseline.
- Stage 18.2 source now has explicit database relevance ranking and query-wide facet semantics; runtime verification is pending.

## Next required action

1. Pull the latest Stage 18.2 branch into Codespaces.
2. Run `tests/Feature/Search/EloquentSearchCatalogRankingTest.php`, `tests/Feature/SearchFlowTest.php`, `tests/Feature/SearchResultsTest.php` and `tests/Feature/PublicCatalog/PublicCatalogBrowseTest.php` through the governed PostgreSQL focused-test lane.
3. Run PHPStan/Larastan and fix any type/style issue without weakening gates.
4. If focused evidence is green, continue the remaining Stage 18.2 public-surface polish only where tests/UX expose a concrete gap; do not expand provider scope.
5. Run `./songchart candidate --prepare`, then canonical verification on the exact committed tree when Stage 18.2 acceptance is complete.

## Documentation checkpoint discipline

For every logical implementation commit:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` as durable project overview/bootstrap, not current-stage state storage;
- keep `docs/project/DEVELOPMENT_STATE.md` as the operational current-state owner;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- keep completed-stage chronology in `docs/project/DEVELOPMENT_HISTORY.md` after governed acceptance;
- never duplicate workflow authority into `AGENTS.md`, `CLAUDE.md`, `GEMINI.md` or nested compatibility copies.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift. Prefer the secret-redacted `./songchart ai doctor` bundle over manually copying raw environment/log output.

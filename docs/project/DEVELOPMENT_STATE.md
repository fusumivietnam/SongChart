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

- Stage 18.1 rich provider-normalized evidence and governed import preview/plan baseline;
- canonical public routes/pages for Artist/Group, Release, Recording, Work and Collection already exist as the starting surface;
- public search already uses a `SearchCatalog` read boundary with thin HTTP controllers and deterministic request validation;
- Linux/Docker/Git-only development and verification authority through `./songchart`.

## Current blockers / risks

- Production `EloquentSearchCatalog` still performs relevance ordering and cross-entity pagination largely after loading bounded per-type result sets instead of making relevance explicit in the PostgreSQL query.
- Facet counts can become scoped to the selected type rather than representing the full query result set.
- `candidate-verification.json` must be reset to Stage 18.2 before candidate closure; volatile successful verification evidence can still make that tracked file dirty and remains a bounded workflow defect rather than a product blocker.
- Shared demo remains optional/unconfigured and does not block product development.

## Latest focused evidence

- Stage 18.1 candidate and canonical verification passed.
- PR #7 merged Stage 18.1 and PR #8 merged the post-18.1 repository hygiene into `main`.
- Existing public SearchFlow/SearchResults/PublicCatalogBrowse coverage is green on the accepted baseline.

## Next required action

1. Establish the Stage 18.2 executable task contract and validation record.
2. Move production relevance semantics into the PostgreSQL-backed search read model with exact → prefix → substring ranking and deterministic canonical tie-breaks.
3. Keep facet counts global to the query while type filters only scope the displayed result page.
4. Add focused PostgreSQL-backed tests for ranking, facets, pagination and canonical URLs without enabling provider API calls in request paths.
5. Run focused tests/PHPStan, then `./songchart candidate --prepare` and canonical verification on the exact committed tree.

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

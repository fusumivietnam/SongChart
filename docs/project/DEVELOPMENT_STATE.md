# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- `main` contains merged Stage `18.2 — Public Search & Canonical Surfaces` via PR #9.
- Stage 18.2 candidate and canonical verification passed with all 27 registered gates successful before merge.
- Stage 18.2 adds PostgreSQL-backed exact → prefix → substring relevance, deterministic canonical tie-breaks, query-wide facets and canonical public search/detail URLs without provider request-path coupling.
- Development authority is Linux/WSL2 + Docker through the repository `./songchart` CLI. GitHub Codespaces is the preferred remote adapter; native Windows Batch/PowerShell, Laragon execution paths, ZIP handoff and patch-installer workflows are retired.

## Current stage

- Stage `18.2 — Public Search & Canonical Surfaces`
- Candidate: `v1`
- Branch: `chore/post-18.2-verification-idempotence`
- Candidate closure: accepted on the merged Stage 18.2 target tree; this branch is a bounded post-acceptance workflow corrective and adds no product behavior.

## Implemented slices

- Stage 18.2 production PostgreSQL relevance and deterministic search ordering accepted;
- global query facets preserved while entity filters scope displayed results;
- strict-Eloquent-safe search projection metadata and canonical public detail routes accepted;
- canonical verification runtime evidence is being separated from tracked `candidate-verification.json`;
- canonical evidence recording now refuses closure when verification has mutated tracked source.

## Current blockers / risks

- The verification-idempotence corrective still requires one candidate/canonical cycle proving `./songchart verify` PASS leaves `git status` clean.
- Runtime verification evidence must only be accepted when stage, candidate and recorded Git commit match the current exact HEAD.
- Shared demo remains optional/unconfigured and does not block product development.

## Latest focused evidence

- Stage 18.2 focused PostgreSQL search/public-catalog tests passed after the strict-Eloquent search-rank corrective.
- PHPStan/Larastan closure passed after explicit comparator iterable types were restored.
- Stage 18.2 candidate verification passed.
- Stage 18.2 canonical verification passed with 27/27 gates and `closure_ready=true`.
- PR #9 merged Stage 18.2 into `main` on 2026-08-26.

## Next required action

1. Verify the post-18.2 idempotence corrective: candidate and canonical verification must pass without modifying tracked files.
2. Confirm runtime evidence is stored under ignored runtime state and is accepted only for the exact current HEAD.
3. Merge the bounded corrective after the clean-tree invariant is demonstrated.
4. Branch from updated `main` for `Stage 18.3 — Public Metadata & SEO Readiness`.
5. Keep Stage 18.3 product-focused: canonical metadata, structured data, indexability, sitemap and duplicate/canonical-link verification.

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

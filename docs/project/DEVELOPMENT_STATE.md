# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.3 — Public Metadata & SEO Readiness` merged to `main` via PR #11 after exact-head canonical closure passed.
- Public canonical metadata/SEO, provider-neutral canonical surfaces, System Settings provider configuration, credential-pool convergence, bounded non-production 2FA disable mode, demo runtime and candidate runtime evidence are accepted baseline.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub Codespaces is the preferred remote adapter.

## Current stage

- Stage `18.3.1 — Verification & AI Workflow Convergence`
- Candidate: `v1`
- Branch: `stage-18.3.1-verification-ai-workflow-convergence`
- Candidate closure: pending.

## Implemented slices

- Stage 18.3.1 task contract records non-goals, official sources, native capability assessment and exact acceptance criteria.
- `./songchart impact` exposes planned path impact; `./songchart impact --diff` resolves actual branch/working-tree changes after implementation.
- Impact resolution now combines impact-map path patterns, registered semantic authorities, reverse verification consumers and focused checks.
- `./songchart reconcile` regenerates project context/repository generated authority from the current tree and shows the generated diff without committing.
- `./songchart audit` runs the existing `quality:verify` child checks in collect-all diagnostic mode while explicitly refusing stage/canonical/full-PostgreSQL/frontend-build nesting.
- Impact-map verification now rejects retired/missing Composer commands and missing test targets/globs.
- Runtime artifact ownership verification rejects tracked `storage/framework/**`, `storage/logs/**` and local backup artifacts; this permanently guards the Stage 18.3 canonical schema-snapshot mutation class.
- Delivery authority now defines one-writer-per-surface, safe rebase/cherry-pick synchronization, generated-artifact regeneration and exact-closed-HEAD push discipline.
- AI protocol/task template now require research/native capability review, planned impact, actual-diff impact, reconcile, audit, focused verification, candidate, canonical and delivery.

## Current blockers / risks

- The new workflow code and authority changes have not yet been executed through Pint/PHPStan/Architecture/quality verification on the exact branch tree.
- Enhanced impact-map verification is expected to surface historical stale command/test routes; those must be corrected in the map rather than weakening the verifier.
- Generated project context/repository manifest will be stale until `./songchart reconcile` or candidate preparation runs from the exact final source tree.
- `./songchart audit` intentionally favors diagnosability over runtime speed and must not be treated as closure evidence.

## Latest focused evidence

- Stage 18.3 exact-head canonical verification and `./songchart close` passed before PR #11 merge.
- Stage 18.3.1 implementation commits are present on the stage branch; runtime/focused verification is pending.

## Next required action

1. Fetch/switch to `stage-18.3.1-verification-ai-workflow-convergence` in Codespaces.
2. Run `./songchart impact --diff` and inspect all newly surfaced authorities/consumers/checks.
3. Run the impact-map verifier and Stage 18.3.1 Architecture test; repair stale map targets/aliases at their source.
4. Run `./songchart reconcile`, review generated diffs, and commit only expected generated authority.
5. Run `./songchart audit` to collect independent quality/static/governance failures in one pass, then fix root causes.
6. Run focused Pint/PHPStan/tests, then `./songchart candidate` / `./songchart close` only when the exact tree is clean.

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

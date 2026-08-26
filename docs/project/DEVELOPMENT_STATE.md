# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- `main` contains merged Stage `18.1 — Rich Entity & Multi-Provider Evidence Model` via PR #7.
- Stage 18.1 canonical verification passed before merge and its governed provider-evidence boundaries are the accepted product baseline.
- Development authority is Linux/WSL2 + Docker through the repository `./songchart` CLI. GitHub Codespaces is the preferred remote adapter; native Windows Batch/PowerShell, Laragon execution paths, ZIP handoff and patch-installer workflows are retired.

## Current stage

- Stage `18.1 — Rich Entity & Multi-Provider Evidence Model`
- Candidate: `v1`
- Branch: `chore/post-18.1-repository-hygiene`
- Candidate closure: accepted on the merged Stage 18.1 target tree; this branch is a bounded post-acceptance repository-hygiene corrective and adds no product behavior.

## Implemented slices

- rich provider-normalized entities covering fields, identifiers, relationships, media, destinations, availability, classifications and metrics;
- provider-specific mapper contracts/registry with governed MusicBrainz mapping;
- read-only Admin provider import preview and deterministic preview-to-plan projection;
- fingerprint-checked plan execution reusing the existing provider import orchestrator;
- provider configuration writes behind application commands so controllers remain transport-only;
- Linux/Docker/Git-only development and verification authority through `./songchart`;
- GitHub Codespaces development adapter, isolated PostgreSQL verification runtime and repository-local AI status/doctor tooling.

## Current blockers / risks

- No Stage 18.1 product blocker remains; canonical verification passed and PR #7 is merged.
- The tracked `candidate-verification.json` evidence file can be rewritten by verification after a successful run, leaving an otherwise verified tree dirty. Treat this as a workflow-hygiene defect to remove before the next stage closure cycle.
- Historical documentation still contains a small number of stale statements from the retired README-current-stage and Windows/Laragon workflow eras; remove only proven residual pointers, not historical evidence.
- Shared demo remains optional/unconfigured and does not block product development.

## Latest focused evidence

- Stage 18.1 candidate verification passed.
- Stage 18.1 canonical verification passed with all registered gates successful.
- PR #7 merged Stage 18.1 into `main` on 2026-08-26.
- Full PostgreSQL test execution reached 385 passing tests before stale Architecture contracts were reconciled; the corrected exact tree subsequently passed canonical verification.
- Linux/Docker/Git is the sole active development/verification route in current machine contracts.

## Next required action

1. Complete this bounded post-18.1 repository-hygiene corrective: remove misplaced/non-authoritative residue and reconcile current documentation ownership.
2. Make verification evidence idempotent so successful verification does not leave tracked source/evidence dirty solely because of volatile runtime metadata.
3. Keep historical Stage 11/12 manifests only where they remain referenced as historical evidence; do not extend them with new chronology.
4. After hygiene verification, branch from updated `main` for `Stage 18.2 — Public Search & Canonical Surfaces` with a new executable task contract.
5. Prioritize user-visible search/browse/canonical-page value over additional tooling expansion.

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

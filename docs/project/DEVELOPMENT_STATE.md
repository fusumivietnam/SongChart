# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- `main` contains the merged Stage `18.0 — YouTube Media Experience` baseline used to start the current Stage 18.1 branch.
- Stage 18.1 is **not accepted yet**; canonical closure for the exact target tree is still pending.
- Development authority is Linux/WSL2 + Docker through the repository `./songchart` CLI. GitHub Codespaces is the preferred remote adapter; native Windows Batch/PowerShell and Laragon execution paths are retired.

## Current stage

- Stage `18.1 — Rich Entity & Multi-Provider Evidence Model`
- Candidate: `v1`
- Branch: `stage-18.1-rich-entity-multi-provider`
- Candidate closure: pending.

## Implemented slices

- rich provider-normalized entities covering fields, identifiers, relationships, media, destinations, availability, classifications and metrics;
- provider-specific mapper contracts/registry with governed MusicBrainz mapping;
- read-only Admin provider import preview and deterministic preview-to-plan projection;
- fingerprint-checked plan execution reusing the existing provider import orchestrator;
- provider configuration writes moved behind application commands so controllers remain transport-only;
- Node 24 development/CI/verification baseline;
- GitHub Codespaces Docker adapter with private forwarded live-demo URL and no auto-start on Codespace open;
- shared demo adapter with remote PostgreSQL and on-demand app/queue/Redis compute;
- repository-local AI skills normalized for Codex-compatible `SKILL.md` frontmatter;
- Linux `./songchart ai status` bootstrap for Git, candidate, context, runtime, demo and AI-tool visibility;
- `./songchart ai doctor` copy-friendly diagnostic bundle for ChatGPT/Codex handoff without exposing environment files or known secret values;
- isolated focused-test lane through `./songchart dev test`, keeping Feature tests on the PostgreSQL verification runtime rather than the development container;
- GitHub-only development handoff authority; native Windows/Laragon wrappers and legacy ZIP/patch installer workflow retired.

## Current blockers / risks

- Candidate evidence is not yet closure-ready; `candidate-verification.json` still records no completed closure run for Stage 18.1.
- Repository context/generated authority is expected to require refresh after the Linux/Git workflow authority changes; refresh only through the governed candidate preparation flow.
- Shared demo is implemented but remains unconfigured until remote PostgreSQL secrets are provisioned; this does not block Stage 18.1 closure.
- Alternative AI tooling such as Gemini/Antigravity is deferred until after launch-readiness work; ChatGPT Plus + GitHub + Codespaces/Codex remains the active development workflow.

## Latest focused evidence

- Codespaces development stack reached healthy `app`, PostgreSQL, Redis and queue services with the private port-8000 preview URL.
- Focused Feature tests run against isolated PostgreSQL 18 verification state; test database safety reports development/test isolation PASS.
- Provider Admin configuration projection and model/static typing were corrected; PHPStan regressions were reduced to zero before the latest stage closure attempt.
- Verification runtime now supplies deterministic testing environment input and captures focused-test warning/failure evidence for `ai doctor`.
- Stage closure exposed and corrected two stale test contracts: `ReproducibleVerificationEnvironmentTest` now asserts Node 24, and `ProviderImportOrchestrationTest` supplies `ProviderRuntimeConfiguration` to direct job execution.
- Linux/Docker/Git is now the sole active development/verification route in machine contracts; native Windows/Laragon wrappers and legacy patch installers have been removed.
- Full Stage 18.1 candidate/canonical closure has not yet been recorded for this corrected exact tree.

## Next required action

1. Synchronize the Codespace to the latest remote Stage 18.1 branch and confirm a clean working tree.
2. Run the two regressions that failed the previous stage attempt: `tests/Architecture/ReproducibleVerificationEnvironmentTest.php` and `tests/Feature/Providers/ProviderImportOrchestrationTest.php`.
3. Run the impact-owned AI/Docker/repository/static gates, including PHPStan, and resolve any remaining active references to retired Windows/Laragon execution surfaces.
4. Run `./songchart candidate --prepare` to refresh/commit generated authority for the changed registered contracts, then confirm `./songchart ai status` reports a clean tree, `Checkpoint: SYNCED`, and `Context: FRESH`.
5. Run `./songchart candidate` on the exact committed tree.
6. Run `./songchart verify` only after candidate PASS; merge Stage 18.1 only after canonical closure is recorded for that exact tree.

## Documentation checkpoint discipline

For every logical implementation commit:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` limited to the current-stage pointer and durable project overview;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- move completed-stage chronology to `docs/project/DEVELOPMENT_HISTORY.md` only after governed acceptance;
- never duplicate workflow authority into `AGENTS.md`, `CLAUDE.md` or `GEMINI.md`.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift. When debugging evidence needs to move between Codespaces/Codex and ChatGPT web, prefer the secret-redacted `./songchart ai doctor` bundle over manually copying raw environment/log output.

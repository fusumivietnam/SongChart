# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- `main` contains the merged Stage `18.0 — YouTube Media Experience` baseline used to start the current Stage 18.1 branch.
- Stage 18.1 is **not accepted yet**; canonical closure for the exact target tree is still pending.
- Development authority remains Docker-first through the repository `songchart` CLI. Codespaces is a remote adapter over the same Docker contract; Windows/macOS/WSL use their supported host entrypoints.

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
- Node 24 development/CI baseline and candidate-preparation workflow hardening;
- GitHub Codespaces Docker adapter with private forwarded live-demo URL and no auto-start on Codespace open;
- shared demo adapter with remote PostgreSQL and on-demand app/queue/Redis compute;
- repository-local AI skills normalized for Codex-compatible `SKILL.md` frontmatter;
- cross-platform `songchart ai status` bootstrap for Git, candidate, context, runtime, demo and AI-tool visibility.

## Current blockers / risks

- Candidate evidence is not yet closure-ready; `candidate-verification.json` still records no completed closure run for Stage 18.1.
- Shared demo is implemented but remains unconfigured until remote PostgreSQL secrets are provisioned.
- Gemini CLI authentication must use Google OAuth (`oauth-personal`) to consume eligible Google AI Pro/Ultra quota; API-key mode is a separate quota/billing path.

## Latest focused evidence

- Codespaces development stack reached healthy `app`, PostgreSQL, Redis and queue services with the private port-8000 preview URL.
- `./songchart ai status` reports Stage 18.1 / v1 and fresh generated project context.
- Full Stage 18.1 candidate/canonical closure has not yet been recorded.

## Next required action

1. Pull the latest branch and restore a clean working tree; local AI-client state such as `.gemini/` must remain untracked/ignored.
2. Confirm Gemini auth is `oauth-personal` when using Google AI Pro.
3. Run `./songchart ai status` and resolve any `Checkpoint` or `Context` drift before implementation.
4. Continue Stage 18.1 only through the current task contract and impact-driven focused tests.
5. Before candidate closure, update this checkpoint in the same logical changeset, then run the governed candidate preparation/verification flow.

## Documentation checkpoint discipline

For every logical implementation commit:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, focused evidence or next action changes;
- keep `README.md` limited to the current-stage pointer and durable project overview;
- keep `docs/project/docs/ROADMAP.md` limited to current/future direction, not delivered chronology;
- move completed-stage chronology to `docs/project/DEVELOPMENT_HISTORY.md` only after governed acceptance;
- never duplicate workflow authority into `AGENTS.md`, `CLAUDE.md` or `GEMINI.md`.

Before handing work to another AI/device, `./songchart ai status` must show the intended branch/stage and no unresolved checkpoint/context drift.

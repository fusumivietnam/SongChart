# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- `main` contains merged Stage `18.2 — Public Search & Canonical Surfaces` via PR #9.
- Post-18.2 verification-idempotence corrective merged via PR #10.
- Stage 18.2 candidate/canonical verification passed with all 27 registered gates successful, and exact-HEAD runtime evidence now leaves tracked Git state clean.
- Development authority is Linux/WSL2 + Docker through the repository `./songchart` CLI. GitHub Codespaces is the preferred remote adapter; native Windows Batch/PowerShell, Laragon execution paths, ZIP handoff and patch-installer workflows are retired.

## Current stage

- Stage `18.3 — Public Metadata & SEO Readiness`
- Candidate: `v1`
- Branch: `stage-18.3-public-metadata-seo-readiness`
- Candidate closure: pending.

## Implemented slices

- Stage 18.2 PostgreSQL search/canonical public surfaces accepted;
- canonical verification evidence is runtime-only and exact-HEAD scoped;
- Stage 18.3 task contract and validation record established.

## Current blockers / risks

- Codespaces development PostgreSQL persists in a Docker named volume during normal `dev down`/verification, but that volume can be lost on Codespace rebuild/deletion or explicit volume removal.
- Development setup currently migrates and seeds provider registry only; a manually created Super Admin is not automatically recreated after development-volume loss.
- Canonical/test `migrate:fresh` is isolated to `songchart_verify_test` and is not the source of development-account deletion.
- `compose.demo.yml` currently expects `.env.demo` and an externally reachable PostgreSQL database but has no governed `.env.demo.example`/CLI bootstrap, so live URL verification is not yet repeatable.

## Latest focused evidence

- Stage 18.2 canonical verification passed with 27/27 gates.
- Post-18.2 idempotence corrective canonical verification passed on exact HEAD with `git status` clean and `source=runtime-exact-head`.
- PR #10 merged the corrective into `main` on 2026-08-26.

## Next required action

1. Add bounded development-data resilience: explicit local Super Admin bootstrap without repository default credentials and a recoverable Codespaces development-data path.
2. Make the demo runtime self-service for Codespaces live URL/canonical metadata verification without sharing the verification database.
3. Implement canonical title/description/link metadata for public entity pages.
4. Add Open Graph/social metadata and structured data.
5. Implement sitemap/indexability/duplicate-content policy and focused verification.
6. Run `./songchart candidate --prepare` and canonical verification on the exact committed tree.

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

# Stage 18.3 Validation Report — Public Metadata & SEO Readiness

Status: current-stage validation record; verification pending.

## Accepted baseline

- Stage 18.2 public search/canonical surfaces merged via PR #9.
- Post-18.2 verification-idempotence corrective merged via PR #10.
- Canonical verification now records runtime evidence outside tracked source and exact-HEAD closure leaves Git clean.

## Current preflight findings

- Canonical/test PostgreSQL `migrate:fresh` is confined to the isolated verification database `songchart_verify_test` and guarded before/after schema preparation.
- Development PostgreSQL uses the separate `songchart-dev` project and persistent named volume `songchart_dev_pgdata`; normal `dev down`, candidate and canonical verification do not remove that volume.
- A Codespaces rebuild/deletion or explicit Docker volume removal can still destroy the development volume. Current development setup migrates and seeds provider registry only, so a manually created Super Admin is not automatically recreated after volume loss.
- `compose.demo.yml` expects `.env.demo` and an externally reachable PostgreSQL database, but the repository currently has no `.env.demo.example` or governed demo CLI/bootstrap path. Codespaces live-demo configuration therefore needs a bounded correction before SEO host/canonical URL verification can be considered repeatable.

## Verification required

- focused tests for any local Super Admin bootstrap/data-preservation correction;
- focused demo configuration/URL guardrails;
- public metadata, canonical-link, structured-data and sitemap/indexability tests;
- PHPStan/Larastan and Pint;
- candidate and canonical verification on the exact committed tree.

## Closure rule

Do not mark Stage 18.3 accepted until host-dependent canonical metadata has been verified through the governed demo/runtime path and canonical verification passes with a clean tracked tree.

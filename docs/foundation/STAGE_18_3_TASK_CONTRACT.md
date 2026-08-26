# Stage 18.3 Task Contract — Public Metadata & SEO Readiness

Status: current-stage task contract.

## Goal

Make SongChart public canonical surfaces production-ready for search engines and social sharing without changing canonical identity or provider-governance boundaries. Stage 18.3 owns canonical metadata, structured data, canonical links, sitemap/indexability policy, duplicate-content protection and live public verification.

## Non-goals

- New provider ingestion pipelines or provider breadth expansion.
- Public request-path provider API calls.
- Canonical mutation from metadata or sitemap generation.
- Personalized discovery, follows or private collections.
- Provider destination/media ranking redesign; that remains Stage 18.4.

## Acceptance criteria

- Artist/Group, Release, Recording and Work canonical pages expose deterministic document title and meta description derived only from canonical/public read models.
- Canonical link URLs are SongChart-owned and independent from provider identity.
- Open Graph/social metadata is deterministic and provider-neutral.
- Structured data uses explicit SongChart canonical URLs and does not expose raw provider payloads/secrets.
- Sitemap/indexability policy covers supported public canonical surfaces and excludes non-indexable/private/development/admin routes.
- Duplicate-content handling and canonical-link behavior are verified by focused tests.
- Public metadata generation performs no provider HTTP requests and no canonical writes.
- A governed live-demo path exists for validating host-dependent canonical URLs in Codespaces without weakening development database isolation.

## Preflight correctness work

Before broad metadata implementation, Stage 18.3 may include bounded corrections required for reliable live verification:

- preserve development data across normal verification workflows and document the actual lifecycle boundary of the Codespaces Docker PostgreSQL volume;
- provide deterministic local Super Admin bootstrap only when explicit local credentials are configured, without shipping default privileged credentials;
- make the demo runtime self-service enough to validate APP_URL/canonical metadata on the forwarded Codespaces URL while reusing the intended development database safely.

These corrections must not create a second database authority or weaken the isolated `songchart_verify_test` lane.

## Affected modules and boundaries

- public canonical controllers/read models/views/layout metadata
- route/canonical URL helpers
- sitemap/indexability implementation
- focused public metadata/SEO tests
- bounded Docker/Codespaces demo bootstrap needed for live URL verification
- local-only development bootstrap safeguards where required

## Expected files

- `docs/foundation/STAGE_18_3_TASK_CONTRACT.md`
- `docs/foundation/STAGE_18_3_VALIDATION_REPORT.md`
- `docs/project/DEVELOPMENT_STATE.md`
- `candidate-verification.json`
- public layout/metadata helpers/read models
- sitemap/indexability implementation
- focused Feature/Architecture tests
- generated repository authority outputs required by candidate preparation

## Authority and safety

- `docs/project/DEVELOPMENT_STATE.md` owns current operational stage state.
- PostgreSQL verification remains isolated in `songchart_verify_test`; `migrate:fresh` must never target the development database.
- Development/live-demo data may not be destroyed by candidate or canonical verification.
- No privileged demo/local account may be created from hard-coded repository credentials.
- Canonical metadata must never depend on raw provider payload or provider identity.

## Verification plan

- focused metadata/canonical-link/structured-data/sitemap tests;
- development-database safety and live-demo configuration guardrails;
- PHPStan/Larastan and Pint;
- `./songchart candidate --prepare` on the exact committed tree;
- `./songchart verify` for canonical closure, which must leave tracked Git state clean.

## Rollback

Revert Stage 18.3 metadata/demo/bootstrap changes together. No provider-ingestion or canonical identity rollback is permitted or required.

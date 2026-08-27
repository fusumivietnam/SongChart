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

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md` for repository workflow and supported execution boundaries.
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md` for source hierarchy, freshness and native-capability rules.
- `docs/project/DEVELOPMENT_STATE.md` for the current stage and closure state.
- `docs/project/stack/docker-development-contract.json` and `docs/project/stack/runtime-environments.json` for development/demo/verification topology.
- `docs/project/engineering/verification-topology.json` and `docs/project/stack/release-pipeline-contract.json` for stage/canonical verification ownership.
- `docs/project/domain/application-data-boundary.json` for controller/query separation and public read boundaries.
- `docs/project/domain/domain-contracts.json`, `docs/project/domain/schema-ownership.json` and the accepted Stage 18.2 public canonical surface as identity/read-model authority.

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.5`; canonical image PHP 8.5 | `composer.json`, `docker/verify/Dockerfile` |
| Laravel | `^13.0`; current runtime 13.25.0 during Stage 18.3 verification | `composer.json`, locked dependencies/runtime evidence |
| PostgreSQL | 18.4 development/canonical image | `compose.dev.yml`, `compose.verify.yml` |
| Redis | 7.4 development/demo runtime | `compose.dev.yml`, `compose.demo.yml` |
| Node.js | repository-governed major 24 | `docs/project/stack/stack-manifest.json`, `docker/verify/Dockerfile` |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | Laravel URL generation and request/routing documentation/source | Absolute route URLs, trusted proxy/root URL behavior and canonical host generation | 2026-08-27 |
| Google Search Central | Sitemap and robots guidance | XML sitemap/indexability and robots crawling policy | 2026-08-27 |
| Schema.org | Schema.org vocabulary | `Person`, `MusicGroup`, `MusicAlbum`/release-oriented, `MusicRecording` and `MusicComposition` structured-data types and canonical URLs | 2026-08-27 |
| Open Graph protocol | Open Graph protocol specification | Deterministic `og:title`, `og:description`, `og:url` and social metadata semantics | 2026-08-27 |

### Native capability assessment

- Capability owner: Laravel public HTTP/routing/view stack plus web-standard metadata protocols.
- Native/first-party capability available: partial.
- Selected official API or primitive: Laravel named routes/URL generation, request host/proxy handling, Blade layout rendering and ordinary HTTP responses; standards-based `<link rel="canonical">`, robots meta, Open Graph/Twitter tags, JSON-LD, `robots.txt` and sitemap XML.
- Why it satisfies the requirement: Laravel already provides the routing/URL/view primitives needed to render deterministic metadata from SongChart read models. No third-party SEO package is required for Stage 18.3 because the required surface is bounded and does not need CMS-style dynamic SEO management.

### Custom implementation justification

- Custom code required: yes, narrowly.
- Missing official behavior: Laravel does not define SongChart's canonical identity policy, entity-specific metadata copy, provider-neutral structured-data mapping, sitemap inclusion/exclusion policy or query/noindex semantics.
- Narrow custom boundary: application read/query helpers and Blade metadata rendering that consume canonical/public read models only, plus sitemap/robots controllers and focused policy tests.
- Framework primitives reused: Laravel routing, URL generation, dependency injection, controllers, Blade views, response objects and PostgreSQL-backed read queries.
- Non-goals: no SEO package framework, no provider HTTP calls from public metadata paths, no canonical writes, no provider identity leakage, no ingestion/provider expansion and no sitemap scale redesign in this stage.

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

## Tests and verification

- `tests/Feature/PublicMetadataSeoTest.php` verifies deterministic metadata, canonical links, robots policy, Open Graph/Twitter and JSON-LD on public canonical surfaces.
- `tests/Feature/PublicSitemapTest.php` verifies sitemap/robots inclusion and exclusion behavior.
- `tests/Architecture/Stage183WorkflowErgonomicsTest.php` guards the bounded demo/queue/system-settings workflow corrections needed for live verification.
- Relevant existing public catalog/search regressions must stay green so Stage 18.3 does not change Stage 18.2 canonical identity behavior.
- `vendor/bin/pint --test` and `vendor/bin/phpstan analyse` remain mandatory through `quality:verify`.
- PostgreSQL-backed Feature tests and the isolated canonical lane remain authoritative; development/demo data is non-authoritative test data and must remain preserved across normal verification.
- Live Codespaces demo verification must confirm canonical/OG/JSON-LD/sitemap/robots URLs use the forwarded port-8001 host.
- Final closure owner is `./songchart close`, which refreshes generated authority and executes canonical verification on the exact committed tree.

## Rollback

Revert Stage 18.3 metadata/demo/bootstrap changes together. No provider-ingestion or canonical identity rollback is permitted or required.

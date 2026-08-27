# Stage 18.3 Validation Report — Public Metadata & SEO Readiness

Status: current-stage validation record; implementation complete for the initial candidate, runtime verification pending.

## Accepted baseline

- Stage 18.2 public search/canonical surfaces merged via PR #9.
- Post-18.2 verification-idempotence corrective merged via PR #10.
- Canonical verification records runtime evidence outside tracked source and exact-HEAD closure leaves Git clean.

## Implemented preflight corrections

- Canonical/test PostgreSQL `migrate:fresh` remains confined to isolated `songchart_verify_test`; Stage 18.3 does not weaken that boundary.
- Development PostgreSQL remains `songchart_docker` in the persistent `songchart-dev` volume.
- Optional local administrator recovery reuses `admin:ensure-local` when `SONGCHART_LOCAL_ADMIN_EMAIL` is explicitly configured. Existing password/2FA state is preserved; if the database was recreated, `admin:create` requests a password interactively. No privileged default password is stored in source.
- `./songchart dev db backup|restore` provides recoverable development checkpoints; restore performs a pre-restore backup.
- Demo runtime is governed by `.env.demo.example` and `./songchart demo ...`; it joins the development Docker network and reads `songchart_docker`, never `songchart_verify_test`.
- Demo starts the public app and isolated Redis only by default; it does not start a duplicate queue worker against shared development data.

## Implemented public metadata/indexability surface

- canonical entity pages expose deterministic title, description, canonical URL, robots, Open Graph/Twitter metadata and JSON-LD;
- Artist person/group mapping distinguishes `Person` and `MusicGroup`; music release/recording/work pages use explicit Schema.org music types;
- catalog roots are indexable while query-filtered catalog URLs are `noindex,follow` with query-free canonical roots;
- search pages are `noindex,follow` and canonicalize to `/search`;
- `/sitemap.xml` lists canonical public catalog/index/detail URLs and excludes private collections;
- `/robots.txt` excludes admin/account/development/search crawling and advertises the canonical sitemap;
- controllers remain thin and sitemap data is read through an application query boundary without provider transport or writes.

## Workflow ergonomics implemented

- ordinary `./songchart candidate` is read-only with respect to generated authority;
- `./songchart candidate --prepare` remains the explicit candidate-stage path when a separate candidate run is desired;
- `./songchart dev test --no-build <path>` reuses an existing verify image for rapid focused reruns;
- `./songchart close` prepares/commits generated authority and then runs canonical closure directly; canonical owns the stage lane exactly once, avoiding a duplicate full candidate-stage run before canonical verification.

## Verification required

- `tests/Feature/PublicMetadataSeoTest.php`;
- `tests/Feature/PublicSitemapTest.php`;
- `tests/Architecture/Stage183WorkflowErgonomicsTest.php`;
- relevant existing public catalog/search regression tests;
- PHPStan/Larastan and Pint;
- live Codespaces demo verification on port 8001, confirming canonical/OG/JSON-LD/sitemap/robots URLs resolve to the forwarded demo host;
- `./songchart close` on the exact committed candidate tree.

## Closure rule

Do not mark Stage 18.3 accepted until focused/static/live evidence is green and `./songchart close` completes canonical verification with a clean tracked working tree and exact-HEAD runtime evidence.

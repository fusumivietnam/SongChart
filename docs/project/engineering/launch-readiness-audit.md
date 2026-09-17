# Stage 28 Launch Readiness Audit

Baseline: accepted `main@5db0b6377997d146d86b4fd8c5aedd0a66c189a3`.

This document is a bounded audit surface for Stage 28. It does not replace `stage-plan.json`, production topology, route authority, SEO authority or generated development state.

## Classification

Every finding must be one of:

- `launch_blocker` — materially prevents a safe first production release;
- `post_launch` — useful correction or polish that can ship after launch;
- `insufficient_evidence` — do not implement until runtime/user evidence exists.

## Existing launch foundations confirmed from repository

| Area | Existing owner/evidence | 28.0A posture |
|---|---|---|
| Public home/search/entity/chart routes | `routes/web.php`, Stage 23 public UX | executable owner exists; exact-head verify |
| Robots + sitemap | `RobotsController`, `SitemapController`, `PublicSitemap`, Stage 18.3 | reuse; exact-head verify only |
| Canonical metadata / structured data | Stage 18.3 SEO authority | reuse; exact-head verify only |
| Production topology | `docs/operations/PRODUCTION_TOPOLOGY.md`, Stage 19 | reuse; runtime verify in 28.0B |
| TLS / domain / rollback guidance | Stage 19 production authority | reuse; runtime verify in 28.0B |
| Backup + isolated restore semantics | Stage 19.0.5 ownership | reuse; runtime verify in 28.0B |
| Queue supervision / runtime health | accepted Laravel/Horizon/Pulse/native health ownership | reuse; runtime verify in 28.0B |
| MusicBrainz import/enrichment | executable catalog adapter + governed import pipeline | executable owner exists; operator vertical verify |
| YouTube destination discovery | executable destination discovery + quota/credential/human approval gates | executable owner exists; verify when configured |
| Account saved-item value | Stage 27 user-library implementation + `tests/Feature/UserSavedEntityTest.php` | executable owner + regression coverage found |
| Public search | `SearchController`, `BuildSearchPage`, product-signal failure-degradation contract | executable owner exists; exact-head verify |
| Product signals | Stage 27 aggregate-only search signals | not a launch blocker |

## Initial findings

### A-01 — Stage 28 activation authority

Status: `closed`.

Stage 28 is repository-authored and active. `docs/project/engineering/stage-plan.json` identifies Stage `28.0` as active, `28.0A` as the active tranche, and PR #50 / `stage-28-launch-readiness` as the single work lease. `candidate-verification.json` has also been advanced to Stage `28.0` so exact-head verification cannot record evidence against the prior stage identity.

### A-02 — SEO foundation already exists

Status: `post_launch` for polish; launch verification required.

The repository already exposes `/robots.txt` and `/sitemap.xml` and has Stage 18.3 canonical/indexability ownership. Do not create another SEO subsystem. Stage 28 only verifies representative canonical URLs, sitemap inclusion/exclusion and production host correctness.

### A-03 — Production/recovery foundation already exists

Status: `post_launch` for operational polish; launch verification required.

Stage 19 already owns production topology, health, deployment, rollback and backup/restore semantics. Stage 28 must prove these paths on the release candidate instead of redesigning deployment.

### A-04 — Provider expansion is not launch-critical

Status: `insufficient_evidence`.

MusicBrainz and YouTube provide the current executable external-provider baseline. Wikidata/Discogs/Spotify/Last.fm/ListenBrainz and other documented candidates remain outside launch scope unless a specific primary flow cannot ship without them.

### A-05 — Semantic/UX/UI cleanup is non-blocking by default

Status: `post_launch`.

Minor wording, terminology, spacing, typography and component polish should use fix-while-touching. Only defects that prevent comprehension or completion of a primary launch flow are promoted to `launch_blocker`.

### A-06 — Account saved-item vertical already has executable regression ownership

Status: `post_launch` for polish; exact-head verification required.

Stage 27 already introduced the user-library owner, controller/read-model path and focused feature coverage in `tests/Feature/UserSavedEntityTest.php`. Stage 28 must reuse that flow and only add code if exact-head/browser verification exposes a real completion or authorization failure.

### A-07 — Public search and destination architecture already has executable owners

Status: `post_launch` for polish; exact-head verification required.

Public search is owned by `SearchController` / application search orchestration and the Stage 27 signal recorder degrades independently of the user request. Provider destination rendering remains governed and core page rendering does not require a live provider request. Stage 28 should prove representative behavior rather than add a new search or destination subsystem.

## 28.0A verification targets

1. Anonymous visitor: `/` → `/search` → canonical artist/group/recording/release/work/chart route.
2. Canonical entity: metadata + relationships + safe provider destination rendering without live-provider dependency for page render.
3. Search: valid results, zero-result recovery baseline and no runtime failure when product-signal writes fail.
4. Account: authenticate → save entity → view/remove saved item where account features are enabled.
5. Operator: provider evidence/import → governed review/admission → canonical public read.
6. Indexability: robots, sitemap and representative canonical tags resolve against the production host/configuration.
7. Production safety: migration/preflight/health/queue/backup-restore/rollback authorities remain executable and non-destructive.

## 28.0A exit rule

28.0A can close when the exact Stage 28 head passes repository quality/PostgreSQL/browser verification for representative visitor/account/operator surfaces and no unclassified primary-flow failure remains. Absence of optional provider expansion, site-wide semantic cleanup or broad UI polish does not keep 28.0A open.

No launch blocker is considered closed without repository-owned automated or operator evidence.

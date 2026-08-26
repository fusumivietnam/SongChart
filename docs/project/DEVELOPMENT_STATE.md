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

- explicit local Super Admin bootstrap reuses `admin:ensure-local`; it is enabled only when `SONGCHART_LOCAL_ADMIN_EMAIL` is configured and never stores a default privileged password;
- development PostgreSQL backup/restore is exposed through `./songchart dev db backup|restore`, with automatic pre-restore backup;
- governed demo runtime is self-service through `./songchart demo setup|up|down|status|logs|url`, uses the development `songchart_docker` database through the shared Docker network, and never uses `songchart_verify_test`;
- demo starts no second queue worker by default, preventing duplicate provider/import job consumers against the shared development database;
- public canonical detail pages now expose deterministic document title, meta description, canonical link, robots, Open Graph/Twitter metadata and provider-neutral JSON-LD;
- Artist person/group structured data is separated as `Person` versus `MusicGroup`; Release, Recording and Work map to explicit Schema.org music types;
- public catalog root pages are indexable; filtered catalog URLs and `/search` are `noindex,follow` with query-free canonical roots;
- `/sitemap.xml` publishes supported canonical public surfaces and excludes private collections/admin/search permutations;
- `/robots.txt` explicitly excludes admin/account/development/search crawling and advertises the canonical sitemap;
- workflow ergonomics are streamlined: ordinary `candidate` is read-only, focused tests support `--no-build`, and `./songchart close` prepares generated authority then runs canonical closure with the canonical-owned stage lane exactly once.

## Current blockers / risks

- New Stage 18.3 focused Feature/Architecture tests and PHPStan/Larastan have not yet been executed on the current exact tree.
- Codespaces development PostgreSQL remains a Docker-volume lifecycle asset: `dev down`, candidate and canonical verification preserve it, while Codespace rebuild/deletion or explicit Docker volume removal can still destroy it; use `./songchart dev db backup` for recoverable checkpoints.
- The governed 8001 demo path requires one live Codespaces verification of canonical host metadata before Stage 18.3 closure.
- Sitemap is a single URL set; if canonical volume approaches the protocol limit, split/index pagination belongs to a later scale correction rather than premature complexity now.

## Latest focused evidence

- Stage 18.2 canonical verification passed with 27/27 gates.
- Post-18.2 idempotence corrective canonical verification passed on exact HEAD with `git status` clean and `source=runtime-exact-head`.
- PR #10 merged the corrective into `main` on 2026-08-26.
- Stage 18.3 implementation and regression coverage are committed on the stage branch; runtime validation is pending.

## Next required action

1. Pull the latest Stage 18.3 branch into Codespaces and configure optional local admin bootstrap in `.env.docker` when desired.
2. Run the focused metadata/sitemap/workflow tests, then PHPStan/Larastan; fix root causes without weakening gates.
3. Run `./songchart demo setup`, open `./songchart demo url`, and verify the rendered canonical/OG/JSON-LD/sitemap/robots host is the forwarded 8001 Codespaces host while data matches development.
4. If focused/live evidence is green, run `./songchart close`; this refreshes/commits generated authority and runs canonical closure without a separate duplicate candidate-stage run.
5. Close Stage 18.3 only after canonical PASS leaves tracked Git state clean with exact-HEAD runtime evidence.

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

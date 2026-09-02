# Production Environment and Secrets Contract

Status: Stage 19.0.2 production environment authority.

## Purpose

Define the first-release production environment boundary without committing real secrets or coupling SongChart to a deployment vendor.

`docs/operations/PRODUCTION_TOPOLOGY.md` owns process topology. This document owns environment/secrets expectations for those processes.

## Secret ownership

Real secrets must be injected by the deployment platform/runtime secret store. They must never be committed to `.env.production.example`, tracked configuration, generated authority, logs, screenshots or test fixtures.

The tracked `.env.production.example` is a names-and-safe-defaults template only.

Required secret classes include:

- `APP_KEY`;
- PostgreSQL credentials;
- Redis password when the selected production Redis deployment requires authentication;
- mail transport credentials;
- provider/service credentials for any enabled integration (for example YouTube, Turnstile, PostHog, Sentry or Resend).

A blank credential in the tracked template means “deployment must inject this when required”, not “blank is production-safe”. Providers remain disabled until their credential/policy requirements are satisfied.

## Fail-closed application invariants

When Laravel detects `APP_ENV=production`, `App\Support\Production\ProductionEnvironmentGuard` is invoked during application boot. Production startup is refused when any of these invariants drift:

- `APP_DEBUG=false`;
- `APP_URL` uses HTTPS;
- `DB_CONNECTION=pgsql`;
- `CACHE_STORE=redis`;
- `QUEUE_CONNECTION=redis`;
- `SESSION_SECURE_COOKIE=true`;
- `SONGCHART_ADMIN_2FA_MODE=required`;
- `DESIGN_LAB_ENABLED=false`.

These are first-release safety invariants, not deployment-provider preferences. They preserve the accepted PostgreSQL authority, Redis runtime topology, secure-session boundary and privileged Admin authentication behavior.

## Production template

Use `.env.production.example` as the inventory of production-relevant names and safe defaults. Deployment tooling must supply environment values without copying real secrets back into the repository.

Important differences from local/demo environments:

- debug is disabled;
- design-lab/preview routes are disabled;
- Admin 2FA is required;
- session cookies are secure and encrypted;
- PostgreSQL is the only supported database authority;
- Redis owns queue/cache runtime;
- production mail is not the local `log` transport;
- optional providers start disabled until reviewed/configured.

## Provider/service configuration

Provider taxonomy remains owned by `docs/providers/PROVIDER_TAXONOMY.md` and `ProviderTaxonomy`.

Environment configuration must not create a second provider taxonomy. Enabling an integration does not grant it new category/role/capabilities. Provider credentials are configuration evidence only; provider readiness still depends on taxonomy registration, enabled state, policy approval, credentials and runtime health.

## Process consistency

The same immutable application artifact must receive a compatible production environment across web, queue-worker and scheduler processes. Process-specific lifecycle settings may differ, but application identity, database, Redis, provider/security configuration and secret ownership must not silently diverge.

## Logging and diagnostics

Production diagnostics must not expose secret values. CI/runtime evidence should report missing/unsafe configuration by variable or invariant name, not by echoing credential contents.

## Out of scope for 19.0.2

- selecting a specific hosting vendor;
- committing Kubernetes/ECS/Compose production manifests before runtime implementation is justified;
- backup/restore procedure (19.0.5);
- alert routing/retention (19.0.4);
- provider breadth expansion;
- production release/tag creation.

## Verification

- unit coverage exercises each fail-closed invariant;
- application boot calls the guard only in production;
- `.env.production.example` contains names/placeholders only and no real credentials;
- existing authentication, database, queue/cache and provider authorities remain unchanged;
- Stage verification and canonical closure remain owned by SongChart verification commands.

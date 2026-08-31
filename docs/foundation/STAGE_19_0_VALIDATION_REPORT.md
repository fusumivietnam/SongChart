# Stage 19.0 Validation Report — Production Readiness & First Release

Status: in progress. Record only verification actually observed for the active Stage 19 tree.

## Accepted baseline

- Stage 18.6 exact sealed head `0ba79a89994b0a2722f5f6c18a84af0045e88cc6` passed candidate and canonical verification.
- GitHub Actions workflow run #161 passed on that exact head.
- PR #15 merged that exact Stage 18.6 head to `main` as accepted merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch `stage-19.0-production-readiness-first-release` was created directly from that accepted merge commit.

## Implemented topology inventory

### 19.0.1 — Production topology + environment inventory

- `docs/operations/PRODUCTION_TOPOLOGY.md` inventories Docker/Compose, Caddy, PHP/Laravel runtime, PostgreSQL 18, Redis, queue, scheduler, health, observability and release/CI surfaces.
- Supported first-release topology is one immutable application artifact/image reused by independent `web`, `queue` and `scheduler` processes behind Caddy, with PostgreSQL 18 and Redis as explicit dependencies.
- Development-only PHP built-in server, bind mounts, local mkcert, debug/local environment defaults and local DB credentials are explicitly excluded from production.
- No production runtime manifest was introduced before environment/secrets ownership is hardened.

No standalone 19.0.1 closure is reused after the tracked 19.0.1.1 corrective changes; the current tree requires fresh verification.

## Active slice

### 19.0.1.1 — Data Contract + Provider Taxonomy Convergence

Implemented source intent now under verification:

- `docs/project/domain/DATA_CONTRACT.md` defines common representations for internal/external IDs, booleans, null/empty semantics, collections, timestamps, partial dates, URLs, reason codes and provider evidence;
- `docs/providers/PROVIDER_TAXONOMY.md` separates provider slug, category, operational role and concrete capability;
- `ProviderCategory`, `ProviderRole` and `ProviderCapabilityCode` are typed machine vocabularies;
- `ProviderTaxonomy` owns the known integration definitions for MusicBrainz, Cover Art Archive, Wikidata, YouTube, PostHog, Sentry, Cloudflare Turnstile and Resend;
- the current persisted `music/analytics/observability/security/email` values remain compatible, avoiding an unnecessary forward migration;
- `Provider` rejects unknown category strings at save time;
- `ProviderCapability` rejects unknown capability codes at save time;
- `ProviderRegistrySeeder` consumes the taxonomy registry and seeds capability rows without resetting existing provider status/enablement state;
- provider operational state is normalized through `ProviderOperationalAssessor` to `disabled`, `unapproved`, `misconfigured`, `degraded`, or `ready`;
- stable machine runtime issue codes distinguish unregistered taxonomy, disabled/unapproved state, missing credentials, unknown/unhealthy runtime health and provider-degraded policy state;
- unknown health is fail-closed as degraded rather than implicitly healthy;
- no migration, provider breadth, route, canonical mutation or generic integration framework is introduced.

Focused regression added:

- known provider category/role/capability classification;
- category typo rejection at the persistence boundary;
- capability typo rejection at the persistence boundary;
- fail-closed missing credential and unknown health behavior;
- ready state only when approved + enabled + configured + healthy evidence align.

## Required focused verification

Pending on the current tree. No PASS is recorded yet.

Run changed-PHP formatting first:

```text
Pint write + --test:
app/Domain/Providers/Enums/ProviderCategory.php
app/Domain/Providers/Enums/ProviderRole.php
app/Domain/Providers/Enums/ProviderCapabilityCode.php
app/Domain/Providers/Enums/ProviderOperationalState.php
app/Domain/Providers/Enums/ProviderRuntimeIssueCode.php
app/Domain/Providers/ProviderTaxonomy.php
app/Domain/Providers/Operations/ProviderOperationalAssessment.php
app/Domain/Providers/Operations/ProviderOperationalAssessor.php
app/Models/Provider.php
app/Models/ProviderCapability.php
database/seeders/ProviderRegistrySeeder.php
tests/Feature/Providers/ProviderTaxonomyTest.php
```

Then run:

```text
./songchart dev test tests/Feature/Providers/ProviderTaxonomyTest.php
focused existing Provider registry/Admin/provider regressions selected by impact
focused PHPStan on changed production PHP + seeder
./songchart impact --diff
./songchart reconcile
./songchart impact --verify
```

If Pint changes any remotely-written PHP locally, integrate/push the formatter-only commit before any further remote source writer takeover.

## Next slice after verified convergence

### 19.0.2 — Secrets/environment hardening

Expected direction, not yet implemented:

- explicit `.env.production.example`/production environment authority without real secrets;
- fail-closed production configuration validation;
- provider/service credential requirements derived from the provider taxonomy/capability contract instead of per-integration ad-hoc formats;
- secure production defaults for environment/debug/session/cache/queue/mail/Admin settings;
- environment injection boundaries before production Docker/Compose runtime implementation.

## Live production evidence position

No real production deployment is claimed by the current tree. Later production smoke, provider/network access and restore drills must remain separate from deterministic candidate/canonical evidence. External network/provider checks are release-confidence evidence only.

## Candidate / canonical closure

- 19.0.1/19.0.1.1 focused/impact verification: pending on current tree.
- Stage 19 candidate: not run on the current tree.
- Stage 19 canonical: not run on the current tree.
- Stage 19 exact closed HEAD: not established.
- First release package/tag: not established.

Any tracked change after a future canonical PASS invalidates closure evidence for that exact HEAD and must be re-verified before delivery.

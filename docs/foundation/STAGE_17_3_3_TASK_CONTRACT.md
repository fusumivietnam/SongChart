# Stage 17.3.3 — Provider Rate Policy & Global Request Gate

Status: implementation candidate.

## Goal

Replace the MusicBrainz-specific request lock with a provider-neutral, operation-aware distributed request-governance boundary shared by Admin/HTTP and queue workers, while implementing only the minimum-interval/cooldown strategy required by the current MusicBrainz live contract.

## Non-goals

- no YouTube adapter or API key admission;
- no generic token-bucket/quota-unit engine without a real provider requirement;
- no Release/Recording/Work ingestion expansion;
- no database migration or package addition;
- no weakening of provider retry, canonical mutation, or verification boundaries.

## Acceptance criteria

- provider adapters obtain rate policy through `ProviderRatePolicyRegistry` and request slots through `ProviderRequestGate`;
- rate state is provider-scoped and shared through the configured Laravel cache/lock backend, which is Redis in Docker dev;
- MusicBrainz Admin search and queue lookup share one global minimum-interval state;
- default MusicBrainz request starts are separated by at least 1100 ms;
- MusicBrainz 429/503 records provider cooldown and respects numeric `Retry-After` when present;
- requests during an active cooldown fail before outbound HTTP dispatch and carry retry-after context;
- Admin provider detail shows strategy, interval, current gate state, and cooldown remaining time;
- `/development/status` includes MusicBrainz rate state in provider readiness;
- policy registry is operation-aware so future YouTube quota semantics can be introduced without changing the adapter/request-gate boundary;
- no migration or package change is required.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/providers/PROVIDER_RATE_POLICY.md`
- `docs/providers/STAGE_17_1_FIRST_LIVE_PROVIDER_CONTRACT.md`
- `app/Support/Providers/Catalog/MusicBrainzProviderCatalogAdapter.php`
- `config/songchart.php`

### Installed versions

No dependency changes. Runtime remains PHP 8.5, Laravel 13, PostgreSQL 18.4 and Redis 7.4 in the Docker development/canonical profiles.

### Official external sources

- MusicBrainz API: https://musicbrainz.org/doc/MusicBrainz_API
- MusicBrainz rate limiting: https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
- YouTube Data API quota calculator: https://developers.google.com/youtube/v3/determine_quota_cost
- YouTube Data API getting started/quota model: https://developers.google.com/youtube/v3/getting-started

### Native capability assessment

Laravel Cache atomic locks and Redis-backed cache state cover the distributed gate needed across HTTP and queue processes. Laravel service-container contracts/singletons cover policy/gate composition. Existing `ProviderRequestException` and Laravel queue retry semantics already own retry propagation. No new package is justified.

### Custom implementation justification

Provider rate semantics are provider-policy rules, not a Laravel generic concern. SongChart needs a small provider-neutral contract that maps official provider limits into Laravel-native distributed locks/cache state. The implementation deliberately supports only `minimum_interval` and `none`; quota-unit strategies are deferred until the YouTube provider is admitted.

## Security, authorization, and data impact

No canonical schema or user authorization changes. Rate-state cache keys contain provider slugs/timestamps/reasons only and no provider payload or secret. Admin operational visibility remains behind existing admin/provider authorization.

## Tests and verification

- `tests/Feature/Providers/ProviderRequestGateTest.php` covers provider-scoped policy/cooldown behavior;
- `tests/Feature/Providers/MusicBrainzProviderCatalogAdapterTest.php` covers shared cooldown and no-dispatch during cooldown;
- `tests/Feature/Admin/MusicBrainzProviderWorkbenchTest.php` covers Admin rate-state visibility;
- provider/catalog, Docker local-development, official-source, documentation, repository-state, taxonomy, repository-contract and candidate gates;
- canonical target closure via `verify-songchart.bat`.

## Rollback

Restore Stage 17.3.2 application/governance files. No database rollback is required.

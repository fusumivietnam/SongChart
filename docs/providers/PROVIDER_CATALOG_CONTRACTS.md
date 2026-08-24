# Provider Catalog Contracts

## Authority

Stage 13.1 defines the provider-facing catalog boundary. It does not perform live HTTP requests, persist raw payloads or mutate canonical catalog models.

## Required flow

`ProviderCatalogAdapter -> ProviderPage/ProviderPayload -> normalize -> NormalizedProviderEntity`

Adapters own provider request mapping and provider-specific normalization. They must not write Eloquent catalog models, select canonical identities or call public controllers.

## Contracts

- `ProviderCatalogAdapter`: declares provider slug, capabilities, pagination fetch and normalization.
- `ProviderCatalogAdapterRegistry`: resolves an adapter by provider slug.
- `ProviderImportContext`: bounded import intent without transport secrets.
- `ProviderPage`: immutable page, cursor and rate-limit state.
- `ProviderPayload`: immutable raw provider entity with deterministic SHA-256 hash.
- `NormalizedProviderEntity`: provider-neutral output for later validation/matching stages.
- `ProviderRequestFailure`: explicit failure category and retryability.

## Tagging

Concrete adapters are registered with the container tag:

`songchart.provider-catalog-adapters`

Provider slugs must be unique. Duplicate registrations fail immediately.

## Forbidden in Stage 13.1

- Live provider HTTP calls.
- API keys or credentials.
- Raw ingestion tables.
- Canonical mutations.
- Fuzzy matching or automatic merging.
- Provider identifiers as canonical primary keys.

## Next stage

Stage 13.2 adds the raw ingestion ledger and audit persistence behind these contracts.

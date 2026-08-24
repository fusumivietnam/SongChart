# Discovery Domain

Stage 16.0 establishes Discovery as a read-oriented product domain over the canonical SongChart catalog.

Discovery owns channel definitions, editorial placement, rule contracts and versioned read projections. It does not own canonical artists, recordings, releases or collections; it does not call provider APIs and it does not mutate provider or canonical catalog state.

The machine-readable authority is the `discovery` section of `docs/project/domain/domain-contracts.json`.

Implementation order after this stage: Entity Rule Engine, manual channels, derived/hybrid channels, projection builder, cache/invalidation, admin composer, public API and public surfaces.

## Stage 16.1

- `rule-engine.md` — executable rule engine, canonical field registry, typed snapshot mapping and deterministic sort contract.

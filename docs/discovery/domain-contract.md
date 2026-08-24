# Discovery Domain Contract

## Aggregate boundary

`DiscoveryChannel` is the aggregate root. A channel has an immutable machine key, mutable editorial slug/name/description, one discoverable canonical entity type, one channel mode, publication state, typed rules, deterministic sorts, semantic presentation and a monotonically increasing revision.

## Canonical entity mapping

The Stage 16.0 discoverable types intentionally follow SongChart canonical vocabulary:

- `artist` — canonical Artist;
- `recording` — user-facing track/recording;
- `release` — user-facing album/single/release;
- `collection` — canonical curated collection.

`work` and `version` remain canonical catalog entities but are not public discovery targets in this contract.

## Modes

- `manual`: editor-owned items; derived rules are prohibited.
- `derived`: rule-owned result set; typed rules are mandatory.
- `hybrid`: derived result plus explicit pins/exclusions; typed rules are mandatory.

## State

`draft`, `active`, `paused`, and `archived` are explicit states. Archived channels are terminal for direct reactivation. Publication eligibility additionally depends on the publication window.

## Limits

- channel default result limit: 1–100;
- manual item position space: 1–500;
- maximum explicit sorts: 3;
- rule conditions: maximum 25;
- rule nesting contract: maximum depth 3.

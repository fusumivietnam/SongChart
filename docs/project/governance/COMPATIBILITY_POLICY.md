# Compatibility and Evolution Policy

Status: system lifecycle authority.

## Principle

SongChart locks semantics, not implementation versions. Frameworks, runtimes, adapters and infrastructure may evolve behind stable capability boundaries. Persisted, public, event and identity contracts require explicit compatibility handling.

## Change classes

### additive
Adds a new optional capability or representation without changing existing meaning. Existing consumers continue to work.

### compatible
Changes implementation or representation while preserving all active consumer semantics.

### migration_required
Changes a durable contract and therefore requires an explicit data/config/event/public compatibility migration.

### breaking
Removes or reinterprets an active contract. Requires owner decision, migration plan, deprecation window where feasible and exact verification evidence.

### destructive
May irreversibly remove data, identity, audit/history, public reachability or security guarantees. Requires explicit owner approval and rollback/recovery evidence before execution.

## Durable surfaces

The following are compatibility-sensitive by default:
- canonical identity and external-identifier semantics;
- persisted schema meaning, PK/FK/unique/delete/nullability semantics;
- public URLs once exposed/indexable;
- queue/event payloads once durable/asynchronous consumers exist;
- provider normalization contracts consumed outside one atomic change;
- configuration keys used outside one disposable environment;
- audit/history retention semantics;
- release/deployment artifact identity.

## Expansion protocol

For data-bearing durable changes prefer:

`expand -> backfill/adapt -> dual-compatible -> switch readers/writers -> observe -> contract`.

Direct replacement is allowed only for disposable/experimental state or explicitly approved pre-data changes.

## Deprecation

Deprecated surfaces must declare:
- replacement or reason no replacement exists;
- whether new feature development is forbidden;
- compatibility window or revisit trigger;
- removal preconditions;
- verification that active consumers no longer depend on the surface.

## Framework and runtime upgrades

Major version upgrades are governed operations, not feature work and not architecture rewrites. Upgrade plans verify the capability boundary, supported dependency ecosystem, runtime extensions, persistence behavior, queue serialization, frontend build, browser behavior and rollback/compatibility path. Version numbers are current targets, never permanent invariants.

## Historical migrations

Historical migrations that may have been applied remain immutable. Pre-data flexibility permits new forward corrections, not rewriting sealed migration history.

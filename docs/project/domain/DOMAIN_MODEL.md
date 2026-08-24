# Domain Contract Registry

Status: canonical human-readable companion to `domain-contracts.json`.

## Authority

For implemented catalog entities, `docs/project/domain/domain-contracts.json` is the machine-readable authority for table, model, field names, nullability intent, writable fields, display fields, identifier semantics and implemented route contracts. Migrations remain the executable storage history; when a planned change requires the two to diverge, update the contract first in the same task and then migrate code/database together.

Aspirational roadmap fields are not added to the registry until the use case that owns them is accepted.

## Canonical entities

| Entity | Table | Display field | Public identity | Admin identity | Optional presentation fields |
|---|---|---|---|---|---|
| artist | `artists` | `name` | `slug` | ULID | `sort_name`, `country_code` |
| work | `works` | `title` | `slug` | ULID | `language_code` |
| recording | `recordings` | `title` | `slug` | ULID | `duration_ms`, `is_explicit` |
| version | `recording_versions` | `name` | `slug` | ULID | `duration_ms` |
| release | `releases` | `title` | `slug` | ULID | `released_on`, `country_code`, `barcode` |
| collection | `collections` | `title` | `slug` | ULID | `description`, `visibility` |

## Rule for cross-entity code

Cross-entity code must resolve schema semantics through `DomainContractRegistry` or a typed entity-specific mapper. It must not infer fields with patterns such as “all entities use `title`” or probe arbitrary attributes with `getAttribute()`.

The registry deliberately records that `RecordingVersion` uses `name`, only `Release` owns `released_on`, and only `Collection` exposes `description` to the generic search presentation layer.

## Change protocol

A new or changed field follows this order:

1. Accepted use case and invariant.
2. Update `domain-contracts.json`.
3. Migration/storage change when required.
4. Eloquent fillable/casts/enum changes.
5. Application DTO/mapper/action changes.
6. Route/API/UI changes.
7. Focused acceptance test plus domain-contract verification.

Undeclared field invention is a contract failure, not an implementation shortcut.

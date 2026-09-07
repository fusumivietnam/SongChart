# Domain Contract Registry

Status: canonical human-readable companion to `domain-contracts.json`.

## Authority

For implemented catalog entities, `docs/project/domain/domain-contracts.json` is the machine-readable authority for table, model, field names, nullability intent, writable fields, display fields, identifier semantics and implemented route contracts. Migrations remain the executable storage history; when a planned change requires the two to diverge, update the contract first in the same task and then migrate code/database together.

Aspirational roadmap fields are not added to the registry until the use case that owns them is accepted. Provider responses, provider identifiers, destinations and artwork are evidence/reference surfaces and do not become canonical identity merely because a provider exposes them.

## Canonical entities

| Entity | Table | Display field | Public identity | Admin identity | Optional presentation fields |
|---|---|---|---|---|---|
| artist | `artists` | `name` | `slug` | ULID | `sort_name`, `country_code`, `artist_type` |
| work | `works` | `title` | `slug` | ULID | `work_type`, `language_code` |
| recording | `recordings` | `title` | `slug` | ULID | `duration_ms`, `is_explicit`, optional `work_id` shortcut |
| version | `recording_versions` | `name` | `slug` | ULID | `version_type`, `duration_ms` |
| release_group | `release_groups` | `title` | `slug` | ULID | `primary_type`, `secondary_types`, `first_release_date`, `disambiguation` |
| release | `releases` | `title` | `slug` | ULID | `release_group_id`, `release_type`, `released_on`, `country_code`, `barcode` |
| collection | `collections` | `title` | `slug` | ULID | `description`, `visibility` |

Artist group/orchestra/choir identity remains an `artist` canonical entity distinguished by `artist_type`; `/groups/*` is a public presentation route, not a second group table/entity.

## Relationships and credits

`entity_relationships` is the durable provider-neutral cross-entity relationship authority. The allowed relationship vocabulary and polymorphic subject/object identity are owned by `domain-contracts.json` and `RelationshipType`.

Stage 20 public relationship/credit projection rules are:

- only verified relationships are public;
- `relationship_type` is the provider-neutral role/relationship vocabulary;
- admitted `metadata.credited_as`, `metadata.join_phrase` and `metadata.position` may refine display/order when present;
- `metadata_source_id` plus `verification_state` preserve provenance/verification;
- provider relationship identifiers or provider-specific payload shapes never become canonical relationship identity;
- `artist_recording` may remain a compatibility/read shortcut where existing code owns it, but it is not a second canonical relationship authority.

The optional `recordings.work_id` shortcut represents only an unambiguous single Work. Lossless Work links, including cases with multiple Work relationships, remain in `entity_relationships`.

## Provider destinations and media

`provider_destinations` is an operational/provider evidence surface attached to canonical entities. It does not define a new canonical entity and does not replace SongChart ULID/slug identity.

Public application projection must expose only approved destinations. Provider availability, embeddability, privacy state and freshness are mutable provider state and may change independently from the canonical music entity. External IDs remain in `external_identifiers`; artwork/media/destination data stays projectable evidence rather than required canonical core fields.

## Stage 20 product authority

The product/domain expansion sequence is owned by:

1. `product-user-journeys.json` — product intent and accepted journey evidence;
2. `provider-reference-matrix.json` — provider reference evidence only;
3. `domain-gap-map.json` — evidence-based gaps and no-change decisions;
4. `canonical-model-proposal.json` — accepted minimal canonical evolution;
5. forward-only migrations where the proposal proves storage/index changes are required;
6. Application Query/Read Model contracts for public composition.

This sequence exists to prevent provider-schema copying and duplicate domain entities.

## Rule for cross-entity code

Cross-entity code must resolve schema semantics through `DomainContractRegistry` or a typed entity-specific mapper. It must not infer fields with patterns such as “all entities use `title`” or probe arbitrary attributes with `getAttribute()`.

The registry deliberately records entity-specific display/date/description semantics, including that `RecordingVersion` uses `name`, `ReleaseGroup` uses `first_release_date`, `Release` uses `released_on`, and `Collection` exposes `description` to the generic search presentation layer.

Controllers remain transport adapters. Public relationship/destination composition belongs in registered Application Query/Read Model surfaces rather than direct controller persistence reads.

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

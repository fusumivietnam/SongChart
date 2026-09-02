# Cross-Boundary Data Contract

Status: active domain authority for data shapes that cross persistence, provider, application, queue, API and presentation boundaries.

## Purpose

SongChart must not represent the same semantic value in multiple ad-hoc formats. Domain-specific authorities still own business meaning; this document owns the common representation rules used when values cross boundaries.

## Canonical representation rules

- Internal entity identifiers are ULID strings. Do not serialize model objects or integer surrogates as public/application identity.
- External/provider identifiers are opaque strings. Never coerce them to integers and never use them as canonical SongChart primary identity.
- Machine state and reason codes are lowercase `snake_case` or dotted lowercase namespaces when namespacing is required.
- Booleans are booleans. Do not use `0/1`, `yes/no` or string `true/false` across typed application boundaries.
- Unknown optional scalar state is `null`; do not substitute empty string, zero or a presentation label for unknown.
- A known empty collection is `[]`; `null` is reserved for unknown/not-observed where the owning contract explicitly permits it.
- Lists are ordered arrays with stable element shape. Maps are associative objects keyed by documented machine identifiers.
- Timestamps crossing an application/API/event boundary are ISO-8601 with timezone. Persisted temporal values use PostgreSQL/Laravel timezone-aware types where chronology matters.
- Partial dates remain partial. Missing month/day must not be invented to force a full date.
- Public URLs must be absolute and HTTPS when they represent an eligible external destination. URLs never define canonical entity identity.
- Locale/language/country values use established standards where available rather than SongChart-specific spellings.
- Scores, confidence and metrics must document scale/unit. Do not compare unrelated raw floats merely because both are numeric.
- Credentials, tokens, cookies and private provider configuration never cross into public DTOs, logs, generated authority or audit snapshots.

## Boundary rule

```text
PERSISTENCE / PROVIDER RESPONSE
          |
          v
TYPED NORMALIZATION / VALUE CONTRACT
          |
          v
APPLICATION DTO / READ MODEL
          |
          v
PRESENTER / API / BLADE
```

Conversion belongs to the semantic owner closest to the source boundary. Controllers and Blade templates must not independently reinterpret enums, dates, null semantics, provider state or evidence.

## Error and reason codes

Machine-readable failures use stable codes; human wording is presentation-only. Prefer namespaced codes such as:

- `provider.credential_missing`
- `provider.health_unknown`
- `provider.runtime_unhealthy`
- `destination.freshness_stale`
- `configuration.invalid`

Changing user-facing Vietnamese/English text must not require changing tests or automation that depend on the machine code.

## Provider evidence

Provider-derived evidence must preserve enough provenance to answer: who observed it, which external resource it came from, what was observed, and when it was checked. Unknown availability/freshness never implies available/fresh.

## JSON configuration rule

JSON/array configuration is reserved for provider-specific optional knobs. Any field that becomes cross-provider policy, authorization, identity, runtime readiness or public behavior must be promoted to a typed contract/owner instead of being copied as repeated JSON keys.

## Compatibility and evolution

- Internal implementation DTOs may evolve with their consumers in one logical change.
- Persisted/event/public contracts require an explicit compatibility or migration strategy when shape or meaning changes.
- Historical migrations remain immutable; forward migrations are used only when the accepted contract requires persisted representation changes.
- Do not create a second universal schema that duplicates owning domain contracts.

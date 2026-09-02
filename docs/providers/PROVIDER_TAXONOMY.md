# Provider Taxonomy Authority

Status: active provider authority for classifying external integrations without conflating provider identity, business role and capability.

## Core rule

Three concepts are independent:

```text
slug       = who the provider is
category   = what broad integration domain it belongs to
role       = how SongChart operationally uses it
capability = what concrete operation it can perform
```

Provider identifiers/resources remain external evidence and never become canonical SongChart primary identity.

## Provider roles

- `data` — external information enters normalization/evidence/canonical-admission boundaries. Example: MusicBrainz.
- `destination` — external media/destination evidence is inspected and projected for outbound/embed use without defining canonical identity. Example: YouTube.
- `service` — SongChart calls an external utility used by the application. Examples: analytics, observability, notification, bot protection and future AI services.

A service provider must not automatically gain catalog import or canonical mutation semantics.

## Categories

Current machine categories are deliberately broad and stable:

- `music`
- `analytics`
- `observability`
- `security`
- `email`
- `ai`
- `storage`
- `search`
- `authentication`
- `utility`

The existing persisted `music` category is retained for compatibility. Operational role and capability provide the finer distinction between catalog data and media destinations.

## Capability namespace

Capabilities use lowercase dotted machine codes. Current owned prefixes:

- `catalog.*` — search, lookup, import, relationships, artwork, enrichment
- `media.*` — search, inspect, embed, outbound
- `analytics.*` — events, pageviews
- `observability.*` — exceptions, performance
- `security.*` — bot protection
- `notification.*` — email and future notification transports
- `ai.*` — chat, embedding, classification, enrichment

Adding a capability requires updating the typed capability owner; arbitrary raw strings are rejected at the persistence boundary.

## Known provider classification

| Provider | Category | Role | Representative capabilities |
|---|---|---|---|
| MusicBrainz | `music` | `data` | `catalog.search`, `catalog.lookup`, `catalog.import`, `catalog.relationships` |
| Cover Art Archive | `music` | `data` | `catalog.artwork` |
| Wikidata | `music` | `data` | `catalog.lookup`, `catalog.enrichment` |
| YouTube | `music` | `destination` | `media.search`, `media.inspect`, `media.embed`, `media.outbound` |
| PostHog | `analytics` | `service` | `analytics.events`, `analytics.pageviews` |
| Sentry | `observability` | `service` | `observability.exceptions`, `observability.performance` |
| Cloudflare Turnstile | `security` | `service` | `security.bot_protection` |
| Resend | `email` | `service` | `notification.email` |

The typed runtime owner is `App\Domain\Providers\ProviderTaxonomy`; this document is the human authority projection.

## Runtime operational state

Provider database state alone is insufficient to claim that an integration is operational. Runtime assessment is based on:

```text
taxonomy registered
+ enabled
+ approved/degraded policy state
+ required credential/config available
+ runtime health evidence
```

The normalized states are:

- `disabled`
- `unapproved`
- `misconfigured`
- `degraded`
- `ready`

Unknown health is not interpreted as healthy. Stable machine reason codes belong to `ProviderRuntimeIssueCode`, while Admin/UI wording may be localized independently.

## Failure isolation by role

- Data provider failures preserve evidence and follow governed retry/rate/admission behavior.
- Destination provider failures fail closed for public eligibility and preserve inspectable evidence.
- Service provider failures use the service contract: optional telemetry should not break the primary request; security/auth dependencies may fail closed; notification work should normally be asynchronous/retryable.

Do not apply one generic failure policy to all provider roles.

## Configuration rule

`providers.configuration` and `provider_capabilities.configuration` are extension points, not alternative schemas. Cross-provider fields that affect readiness, policy, authorization, identity or public behavior must move into a typed owner instead of being repeated as JSON keys.

## Adding a provider

Before adding or enabling a provider:

1. assign an existing category or explicitly extend `ProviderCategory`;
2. assign the operational role;
3. declare only capabilities actually supported through approved code/official API behavior;
4. define credential/config ownership and whether the dependency is required or optional;
5. define health/failure semantics;
6. preserve provider-specific IDs as external identity only;
7. add focused regression for the new taxonomy/capability behavior.

Do not add providers merely to increase provider count.

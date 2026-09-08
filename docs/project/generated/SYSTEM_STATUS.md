# Generated SongChart System Status

> Generated from repository control-plane authorities. Do not edit manually.

## Executive status

- Current stage: `22.0 — implementing`
- Pre-data lifecycle: `development-flexible`
- Registered components: `12`
- Decision-debt items: `5`
- R3/R4 component + decision items: `7`

## Architecture / lifecycle

| Component | Area | Lifecycle | Action | Risk | Stability |
| --- | --- | --- | --- | --- | --- |
| `canonical_identity` | domain | `adopted` | `harden` | `R4` | `locked` |
| `canonical_relationships` | domain | `adopted` | `harden` | `R4` | `locked` |
| `artist_recording_shortcut` | domain | `compatibility` | `investigate` | `R2` | `durable` |
| `canonical_mutation` | application | `adopted` | `harden` | `R4` | `locked` |
| `postgresql` | persistence | `adopted` | `keep` | `R3` | `replaceable` |
| `laravel` | framework | `adopted` | `keep` | `R2` | `replaceable` |
| `php_runtime` | runtime | `adopted` | `keep` | `R2` | `replaceable` |
| `node_runtime` | runtime | `adopted` | `keep` | `R1` | `replaceable` |
| `livewire` | frontend | `adopted` | `keep` | `R1` | `replaceable` |
| `search_projection` | application | `adopted` | `harden` | `R2` | `extensible` |
| `github_codespaces` | development | `adopted` | `keep` | `R1` | `replaceable` |
| `github_hosted_actions` | delivery | `adopted` | `keep` | `R2` | `replaceable` |

## Upgrade radar

| Technology | Current target | Lifecycle | Upgrade action |
| --- | --- | --- | --- |
| `php` | `^8.5` | `adopted` | `review_major` |
| `laravel` | `^13.0` | `adopted` | `review_major` |
| `livewire` | `^4.0` | `adopted` | `review_major` |
| `postgresql` | `18` | `adopted` | `migration_and_query_plan_review` |
| `node` | `24` | `adopted` | `approved_lts_alignment` |
| `vite` | `^7.0.0` | `adopted` | `review_major` |
| `tailwindcss` | `^4.2.0` | `adopted` | `review_major` |
| `playwright` | `1.62.1` | `adopted` | `review_with_browser_evidence` |

## Decision debt

| Decision | Risk | Action | Before data |
| --- | --- | --- | --- |
| `public_slug_durability` | `R3` | `investigate` | yes |
| `deletion_retention_matrix` | `R4` | `investigate` | yes |
| `polymorphic_referential_integrity` | `R4` | `harden` | yes |
| `compatibility_shortcut_lifecycle` | `R2` | `investigate` | yes |
| `production_deployment_target` | `R2` | `investigate` | no |

## Execution profiles

| Profile | Purpose |
| --- | --- |
| `light` | constrained developer hardware or quota fallback |
| `standard` | normal local or Codespaces development |
| `full` | closure and release confidence |

## Roadmap

| Stage | Status | Title |
| --- | --- | --- |
| `21.0` | `accepted` | Editorial Admin UX |
| `22.0` | `committed` | System Control Plane & Pre-Data Stabilization |
| `23.0` | `candidate` | Public Product UX |
| `24.0` | `candidate` | Provider Expansion & Data Quality |
| `25.0` | `candidate` | Observability & Operations |
| `26.0` | `candidate` | Release & Package Pipeline |

Detailed domain, persistence, compatibility and resilience semantics remain owned by their referenced repository authorities; this file is a generated consolidated view.

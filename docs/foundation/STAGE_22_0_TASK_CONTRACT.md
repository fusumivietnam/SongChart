# Stage 22.0 — System Control Plane & Pre-Data Stabilization

Status: implementing.

## Purpose

Create one repository-native control plane over existing SongChart authorities and reduce expensive-to-reverse architecture/data risks before production data and public traffic make structural corrections costly.

This stage is governance and stabilization work. It must not redesign the domain merely because the database is still small.

## Outcomes

1. Central lifecycle/risk projection over existing authorities.
2. Repository-native roadmap that survives chat/AI handoff.
3. Explicit compatibility, migration and deprecation policy.
4. Pre-data freeze state and transition requirements.
5. Database risk inventory focused on identity, PK/FK/type, uniqueness, deletion/retention, polymorphic references and public URL durability.
6. Framework/runtime lifecycle where current versions are targets, not permanent architecture invariants.
7. Resilience hierarchy for GitHub/Codespaces/CI quota/external DB/local hardware constraints.
8. Machine verification that prevents silent drift between control-plane state and stack/runtime authorities.

## Non-goals

- no mass schema reset;
- no PK strategy change without evidence;
- no provider-specific canonical entities;
- no microservice split;
- no repository-pattern rollout;
- no package upgrade solely because a newer version exists;
- no weakening of canonical verification;
- no production data migration in this stage unless an accepted pre-data decision proves it necessary.

## Tranches

### 22.0A — Control Plane & Roadmap Authority

- `CONTROL_PLANE.md`
- `project-control-plane.json`
- repository-native `roadmap.json`
- common lifecycle/action/stability/risk/change classes
- decision-debt registry

### 22.0B — Pre-Data Architecture/Data Stabilization

- pre-data lifecycle state machine
- actual PostgreSQL schema inventory/snapshot evidence
- PK/FK/type/null/default/index/unique/delete behavior review
- deletion/retention matrix
- polymorphic referential-integrity policy and verifier
- public slug/redirect durability decision
- classify each reviewed item as keep/harden/change-before-data/compatibility/deprecated

### 22.0C — Framework/Runtime Lifecycle & Resilience

- current technology targets remain replaceable implementation metadata
- verifiers resolve Node/PostgreSQL target versions from authority instead of historical Stage literals
- framework upgrade protocol
- light/standard/full execution profiles
- GitHub-hosted/self-hosted/local exact-SHA verification fallback hierarchy
- Codespaces optional adapter, not continuity authority
- constrained-hardware policy

### 22.0D — Closure & Generated Views

- generated system status/upgrade radar/decision debt view
- control-plane verification in canonical quality path
- impact mapping for control-plane authorities
- exact-head canonical closure
- owner review of remaining R3/R4 decision debt before Stage 23 activation

## Invariants

- Existing owning authorities remain source authorities; the control plane is a projection/index, not a competing semantic owner.
- Canonical SongChart identity remains provider-neutral.
- Historical migrations that may have been applied are never rewritten for convenience.
- Search/read projections never become source of truth.
- New features target authoritative capability boundaries, not compatibility/deprecated shortcuts.
- Breaking durable changes become compatibility/migration work once the project is data-bearing.
- Quota, Codespaces availability or weak local hardware may change where verification runs, never what semantic evidence is required.
- A local/self-hosted fallback must be tied to exact Git SHA/tree and may not silently bypass owner merge gates.

## Owner decision gates

Owner input is required for:
- canonical identity/entity taxonomy changes;
- destructive/irreversible R4 changes;
- public URL durability policy if alternatives materially change product semantics;
- deletion/retention rules that intentionally discard audit/provenance/history;
- transition from `schema-baseline-approved` to `data-bearing`;
- final merge.

## Verification

Stage 22 must preserve all existing `quality:verify`, PostgreSQL, browser and frontend gates. Control-plane checks are added through an already-owned verification surface rather than creating an unregistered parallel closure command.

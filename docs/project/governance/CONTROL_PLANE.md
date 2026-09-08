# SongChart System Control Plane

Status: human-readable control-plane companion. It does not replace owning authorities.

## Purpose

The control plane provides one place to answer:
- what SongChart currently has;
- which authority owns each durable semantic;
- what is current implementation versus locked invariant;
- what should be kept, hardened, upgraded, migrated, replaced, retired or investigated;
- what risks and decision debt remain before real data/traffic;
- what fallback exists when GitHub, Codespaces, external services, quota or local hardware are constrained.

Owning domain, stack, security, persistence and delivery contracts remain authoritative. `project-control-plane.json` is an index and lifecycle projection over them.

## Lifecycle

Every registered component uses one lifecycle state:

`proposed`, `experimental`, `adopted`, `compatibility`, `deprecated`, `retired`.

Every component also uses one action:

`keep`, `harden`, `upgrade`, `migrate`, `replace`, `retire`, `investigate`.

Lifecycle and action are independent. An adopted component may still be scheduled for upgrade; a compatibility component may remain supported while new feature development is forbidden on it.

## Stability

- `locked`: expensive identity/data semantic; changing it is exceptional.
- `durable`: externally or operationally observable contract; backward compatibility is required.
- `extensible`: stable boundary intended to accept additional implementations.
- `replaceable`: current implementation behind a stable capability boundary.
- `experimental`: not yet promised as durable.

## Risk

- `R0`: disposable/generated/local-only.
- `R1`: easy replacement with bounded impact.
- `R2`: coordinated compatibility change required.
- `R3`: expensive migration/public or operational contract impact.
- `R4`: identity, irreversible data, security or durable public contract risk.

## Change classes

All material changes are classified as `additive`, `compatible`, `migration_required`, `breaking`, or `destructive` and follow `COMPATIBILITY_POLICY.md`.

## Pre-data rule

Before SongChart is marked data-bearing, breaking changes are allowed only when intentionally approved by the owning contract and recorded as `change_before_data`. After the data-bearing transition, persisted/public/event contracts evolve through expand/migrate/contract compatibility steps. Existing applied historical migrations remain immutable regardless of data-bearing state.

## Execution resilience

Development and verification are capability contracts, not Codespaces-only or GitHub-hosted-runner-only implementations. `resilience-matrix.json` owns the primary/fallback hierarchy and execution profiles.

## Human views

Generated/project views may present the control plane as GitHub Projects, Markdown status, Atlas, PR status or dashboards. These are projections. Repository contracts remain source authority.

## Decision rule

A future stage must answer:
1. which accepted use case or journey requires the change;
2. which existing capability already owns relevant behavior;
3. which semantic authority owns the concept;
4. whether the change extends an existing boundary or creates a new capability;
5. whether persistence/public/event/config contracts change;
6. which compatibility and risk classes apply;
7. the migration, deprecation, rollback and verification path.

AI may extend an established authority but must not silently create a competing authority.

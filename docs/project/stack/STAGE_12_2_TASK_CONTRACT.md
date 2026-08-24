# Stage 12.2 Task Contract — Technology Stack Authority & AI Development Guardrails

## Goal

Create human-readable and machine-readable authority for the technology stack, package ownership and AI coding boundaries before provider import development begins.

## Non-goals

- No package additions or upgrades.
- No catalog schema changes.
- No provider API integration.
- No frontend redesign.

## Acceptance criteria

- Stack, framework, package, capability, frontend, database, testing and static-analysis authorities exist.
- Approved, restricted, deprecated and forbidden patterns are explicit.
- `stack-manifest.json` is valid and consistent with dependency manifests.
- `composer stack:verify` is part of `quality:verify`.
- AGENTS and START_HERE require stack authority review.
- Architecture tests protect the authority and verification chain.

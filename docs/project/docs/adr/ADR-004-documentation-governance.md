# ADR-004: Documentation Governance

Status: Accepted

## Context

SongChart has extensive project, provider, operations, extension and UI documentation. Without an explicit authority order and lifecycle, contributors can create parallel documents, copy rules into multiple locations or leave implementation status inconsistent with code.

## Decision

Adopt a documentation-first governance contract with:

- `AGENTS.md` as repository operating rules;
- `docs/START_HERE.md` as the reading router;
- canonical project authorities under `docs/project/docs/`;
- module authorities under their existing module directories;
- task contracts before implementation;
- factual status and manifests after implementation;
- automated checks for required files and local Markdown links.

New documents require distinct ownership, lifecycle or audience. Existing authorities are updated instead of duplicated.

## Consequences

Positive:

- fewer contradictory rules;
- clearer reading order and ownership;
- reproducible AI-assisted development;
- documentation drift becomes testable;
- delivery changes are easier to review.

Trade-offs:

- each task includes a small documentation review cost;
- renamed or moved documents require link maintenance;
- the verifier intentionally checks structure, not prose correctness.

# ADR-001: Modular Monolith

Status: Accepted

## Context

The product has multiple domains but is still under active discovery. Independent services would add deployment, observability and consistency cost before boundaries stabilize.

## Decision

Use one Laravel application and one primary PostgreSQL database, organized into explicit business modules.

## Consequences

Positive:
- simple local development;
- atomic transactions;
- easier refactoring;
- lower operations cost.

Constraints:
- module boundaries must be enforced by convention/tests;
- no cross-module table access without an owned interface;
- extraction remains possible only after measured need.

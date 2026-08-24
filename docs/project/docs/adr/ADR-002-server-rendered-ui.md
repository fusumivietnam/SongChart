# ADR-002: Server-rendered UI

Status: Accepted

## Decision

Use Blade for pages, Livewire for interactive regions, and Alpine.js for small local state.

## Rationale

Catalog and entity pages depend on crawlability, fast first content and stable metadata. A full SPA would add hydration, routing and API duplication without enough product benefit.

## Escape hatch

A separate frontend may be considered only for a clearly isolated experience whose interaction model cannot be delivered maintainably with Livewire.

# SongChart Design Authority

This directory contains the canonical visual and interaction contracts for SongChart.

## Required reading

### Public frontend

Read:

`docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`

### Admin

Read:

`docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`

## Priority order

```text
1. Design contracts
2. Approved design tokens
3. Approved shared components
4. Approved screenshots
5. Page-specific implementation
```

No AI agent or developer may silently introduce a new design direction.

When a requirement conflicts with a contract:

1. document the conflict;
2. propose the smallest system-level change;
3. update the relevant contract;
4. update shared tokens/components;
5. then update page implementations.

Reference screenshots communicate visual intent. Contracts remain authoritative for behavior, accessibility, responsive rules, entity terminology and provider disclosure.

## Implementation baseline

Phase 1 foundation status is documented in:

`docs/ui/PHASE_1_DESIGN_FOUNDATION.md`

New UI work must build on the components under `resources/views/components/ui/`.

## Phase 3 UI inventory

The governed living inventory is documented in `docs/ui/PHASE_3_UI_PREVIEW.md` and rendered under `/ui-preview`. Reusable patterns must appear there before broad feature-page adoption.


## Phase 4 search vertical slice

The governed search implementation is documented in `docs/ui/PHASE_4_SEARCH_FLOW.md`. Search pages must reuse the approved search form, entity result row and provider chooser patterns.

The governed homepage composition is documented in `docs/ui/PHASE_5_HOMEPAGE.md`. Search remains above the fold; homepage discovery must preserve entity labels, provenance and the no-fabricated-metrics rule.

## Phase 6 search results

The governed result-reading experience is documented in `docs/ui/PHASE_6_SEARCH_RESULTS.md`. Facets, result summaries, verification states and pagination must preserve the canonical search URL contract and must not introduce fabricated ranking.

## Stage 07 entity detail system

The governed entity-detail composition is documented in `docs/ui/PHASE_7_ENTITY_DETAIL_SYSTEM.md`. All entity pages use one shared identity/facts/relationships/identifiers/provenance/provider system. Entity-specific differences belong in the catalog payload, not duplicated page templates.

## Stage 08 provider chooser

The governed outbound destination system is documented in `docs/ui/PHASE_8_PROVIDER_CHOOSER.md`. Provider links are actionable only after status, compliance, HTTPS and host-allowlist checks.

## Stage 09 authentication and account shell

The governed authentication and account UI is documented in `docs/ui/PHASE_9_AUTHENTICATION_ACCOUNT_SHELL.md`. Fortify owns endpoint behavior; shared frontend/account components own presentation.

## Stage 10 admin dashboard

The governed operations dashboard is documented in `docs/ui/PHASE_10_ADMIN_DASHBOARD.md`. Dashboard data must come from the shared snapshot boundary, unsupported modules must be disabled rather than linked to `#`, and no chart or metric may be fabricated.

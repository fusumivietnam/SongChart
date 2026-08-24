# Phase 10 — Admin Dashboard

Status: implemented for `0.1.0-dev`.

## Authority

Read together with:

- `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`
- `docs/ui/PHASE_2_SHARED_SHELLS.md`
- `docs/setup/FEATURE_TEST_ASSERTIONS.md`
- `docs/ui/STAGE_10_TASK_CONTRACT.md`

## Attention-first hierarchy

The dashboard renders in this order:

1. page header and context;
2. work requiring attention and explicit next actions;
3. operational summary metrics;
4. data-source status and general notices;
5. technical sync/extension detail behind a secondary diagnostics disclosure.

Charts are intentionally omitted until a real time-series source exists. Do not fabricate historical points merely to satisfy the visual contract.

## Data boundary

`AdminDashboardSnapshot` is the single read boundary for the dashboard. Blade must not issue queries.

The snapshot may read:

- users;
- provider registry;
- provider sync runs;
- extension operations.

If a runtime table does not exist, the dashboard must return an empty collection or zero count and render an explicit empty state. A missing optional operational table must not crash `/admin`.

## No fabricated metrics

Do not hard-code catalog totals, trends, percentages or activity. Until canonical catalog tables exist, the dashboard reports only implemented runtime domains.

## Navigation rules

Unimplemented admin modules must not use `href="#"`. Render them as disabled navigation items with `aria-disabled="true"`. Add a real link only when the route and authorization boundary exist.

## Stable markers

Tests and automation may rely on:

- `data-admin-dashboard="attention-first"`;
- `data-dashboard-section="attention-center"`;
- `data-dashboard-section="metrics"`;
- `data-dashboard-metric`;
- `data-work-item`;
- `data-system-notice`;
- `data-provider-row`;
- `data-sync-status`;
- `data-extension-operation`.

Do not replace behavior assertions with utility-class assertions.

## Regression rules

The pre-Stage-10 dashboard contained an obsolete test label and dead `#` links. When replacing a governed page, remove or rewrite stale tests and placeholder links instead of layering compatibility code around them.

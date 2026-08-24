# Stage 10 Task Contract — Admin Dashboard

## Goal

Deliver the first operations-first admin dashboard using real local runtime data and stable semantic markers.

## Non-goals

- no catalog CRUD;
- no fake chart or fabricated trend data;
- no provider API calls;
- no new analytics dependency;
- no dead routes for modules not implemented yet.

## Acceptance criteria

- admin-only access remains enforced;
- dashboard renders summary metrics, work queue, provider health, sync history, extension activity and system notices;
- missing runtime tables degrade to empty states instead of a 500 response;
- no hard-coded fake catalog totals;
- unsupported sidebar modules render disabled, not as `href="#"` links;
- controller extends the shared application controller;
- Feature tests assert semantic dashboard sections.

## Affected modules

- admin dashboard controller and view;
- admin shell navigation and topbar;
- admin snapshot service;
- admin Feature tests;
- UI governance documentation.

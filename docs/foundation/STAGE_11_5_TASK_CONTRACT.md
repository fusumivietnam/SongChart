# Stage 11.5 Task Contract — Request and Action Normalization

## Goal

Normalize public search and extension administration around Laravel Form Requests and explicit application Actions while preserving existing routes, views, validation behavior and extension domain services.

## Acceptance criteria

- `SearchController` uses a dedicated `SearchRequest`.
- Search query normalization and page orchestration are delegated to `BuildSearchPage`.
- Extension write controllers depend on application Actions, not extension lifecycle managers directly.
- Existing extension Form Requests remain the validation authority.
- Controllers remain responsible only for HTTP concerns, stable flash messages and exception reporting.
- Existing route URLs, authorization gates and user-visible behavior remain unchanged.
- Architecture verification prevents inline search validation and direct extension-manager dependencies from returning.

## Non-goals

- Changing search ranking, result payloads or provider behavior.
- Replacing extension domain services.
- Introducing repositories, DTO frameworks or a generic service layer.
- Moving read-only Eloquent projections out of controllers before a second consumer exists.

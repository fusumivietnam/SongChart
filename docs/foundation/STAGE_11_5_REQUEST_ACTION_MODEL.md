# Stage 11.5 Request and Action Model

## Boundary

The normalized HTTP flow is:

`Route -> Form Request -> Controller -> Application Action -> Domain service/model -> Response`

## Search

- `SearchRequest` owns query validation and normalized accessors.
- `BuildSearchPage` owns catalog invocation, empty-result construction and out-of-range page handling.
- `SearchController` only binds the request/action result to the search view.

## Extension administration

Dedicated Actions wrap extension lifecycle use cases:

- inspect package;
- install package;
- upgrade package;
- toggle plugin;
- activate theme;
- rollback extension;
- clean old releases.

The existing extension managers remain domain/infrastructure policy owners. Actions do not duplicate extraction, signature, migration, snapshot, registry or rollback rules.

## Error boundary

Controllers retain stable browser messages and call Laravel `report()` for install/upgrade failures. Raw exception messages are never exposed to the admin UI.

## Guardrails

- Do not reintroduce `$request->validate()` in public search controllers.
- Do not inject extension managers directly into `ExtensionController`.
- Do not create generic base actions or repositories without multiple concrete consumers.

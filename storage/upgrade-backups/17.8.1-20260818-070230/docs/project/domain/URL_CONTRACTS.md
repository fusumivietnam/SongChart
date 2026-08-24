# Implemented URL Contracts

Status: current-route authority for catalog identity formats.

Public catalog detail URLs are type-specific, plural, slug-based, and canonical:

- `GET /artists/{slug}`
- `GET /release-groups/{slug}`
- `GET /releases/{slug}`
- `GET /recordings/{slug}`
- `GET /works/{slug}`
- `GET /versions/{slug}`
- `GET /collections/{slug}`

`slug` matches `[a-z0-9-]+`.

The generic `/entity/{type}/{slug}` route and the older singular `/artist|release|recording|work/{slug}` aliases are not public contracts. Stage 17.8 intentionally removes them without redirects because SongChart has not yet published/indexed the local catalog.

Implemented admin catalog list: `GET /admin/catalog/{type}`.

Implemented admin catalog detail: `GET /admin/catalog/{type}/{id}`. Admin ID input accepts upper- or lowercase ULID characters; canonical application serialization remains lowercase.

# URL and SEO Contract

## Canonical public URL patterns

- `/artists/{slug}`
- `/release-groups/{slug}`
- `/releases/{slug}`
- `/recordings/{slug}`
- `/works/{slug}`
- `/versions/{slug}`
- `/collections/{slug}`
- `/search?q=...`

Do not expose database IDs unless needed for collision-safe routing. Do not expose the internal `Entity` abstraction as `/entity/{type}/{slug}` on the public surface.

Stage 17.8 does not create legacy redirects because the catalog remains local/unindexed. Once production indexing begins, any future URL change requires an explicit redirect/canonical migration plan.

## Slug policy

- lowercase;
- normalized whitespace;
- punctuation stripped safely;
- deterministic transliteration;
- reserved words rejected;
- future production slug changes require an explicit redirect policy.

## Technical SEO

- canonical URL;
- robots directives;
- XML sitemap split by entity type;
- hreflang only when localized pages truly exist;
- Open Graph and social cards;
- breadcrumbs;
- pagination metadata;
- noindex for thin, empty and internal-filter pages.

## Structured data

Use schema.org types only when page content supports them:
- MusicGroup / Person
- MusicAlbum
- MusicRecording
- BreadcrumbList
- WebSite SearchAction where valid

Structured data must be generated from canonical application data, not handwritten per template.


## Artist subtype public taxonomy

SongChart keeps `Artist` as the canonical domain entity while exposing subtype-aware public URLs:

- `artist_type=person` → `/artists/{slug}`
- `artist_type=group|orchestra|choir` → `/groups/{slug}`

The existing canonical identity, provider identifiers and relationships are unchanged. Legacy generic/singular public detail URLs are not restored and no redirect is required while the catalog remains local/unindexed.

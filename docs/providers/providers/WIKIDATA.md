# Wikidata

Status: recommended P0 enrichment provider.

## Use

- external IDs;
- places, dates, languages, occupations and relationships;
- links to Wikimedia resources.

## Rules

- Use entity APIs for known QIDs.
- Use search APIs for text search.
- Use SPARQL only for narrowly scoped graph queries.
- Do not use expensive regex SPARQL for fuzzy search.
- Cache entity revisions and record retrieved revision ID.
- Treat statements with qualifiers/ranks/references correctly.
- Do not flatten conflicting claims into one value without provenance.
- Wikimedia licenses and attribution must be reviewed for each reused content type.

Official reference:
- https://www.wikidata.org/wiki/Wikidata:Data_access

# Stage 17.1 — First Live Provider Contract

Status: implementation candidate.

## Goal

Introduce MusicBrainz as SongChart's first live metadata provider behind the existing provider-neutral catalog boundary, fail closed until policy/contact configuration is explicit, and make provider/pipeline readiness visible from the local development control center.

## Non-goals

- no new database migration;
- no public-page live provider calls;
- no bulk catalog crawl;
- no release/recording/work ingestion;
- no canonical Artist vertical-slice closure yet;
- no new package or external SDK;
- no authentication/OAuth flow for MusicBrainz user data.

## Acceptance criteria

- MusicBrainz adapter is registered through `songchart.provider-catalog-adapters`;
- only Artist lookup/search capabilities are exposed in 17.1;
- requests use WS/2 JSON, meaningful User-Agent, bounded timeouts, and shared-cache serialization to one request/second;
- live traffic defaults disabled and placeholder contact details fail closed;
- raw payload remains provider evidence and normalizes to provider-neutral Artist fields plus `musicbrainz_artist` MBID;
- `/development/status` reports provider readiness and pipeline snapshot without becoming the business admin dashboard;
- official MusicBrainz API/rate-limit/license references are recorded;
- focused tests cover fail-closed configuration and Artist normalization.
- delivery publishes both a Laragon-ready full source artifact and a baseline-declared incremental changeset under `docs/project/engineering/DELIVERY_WORKFLOW.md`.

## Official sources

- https://musicbrainz.org/doc/MusicBrainz_API
- https://musicbrainz.org/doc/MusicBrainz_API/Search
- https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
- https://musicbrainz.org/doc/About/Data_License

## Verification

Stage closure: `stage-verify.bat` / `songchart.bat test`.
Canonical closure: `verify-songchart.bat` / `songchart.bat verify`.

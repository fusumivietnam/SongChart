# Provider Implementation Roadmap

## Foundation before any provider

- provider registry table;
- provider capability enum;
- provider account/credential storage;
- encrypted user tokens;
- normalized external identity table;
- metadata assertion/provenance model;
- availability table with market and expiry;
- request quota ledger;
- provider health state;
- feature flags and kill switches;
- idempotent import jobs;
- fixture-based contract tests;
- deletion/revocation workflow;
- attribution component registry.

## Iteration 1

Implement MusicBrainz, Cover Art Archive and Wikidata adapters.

Outcome:
- canonical artist/release/recording/work import;
- external identity linking;
- artwork attribution and caching policy;
- provenance visible in admin.

## Iteration 2

Implement YouTube link and embed adapter.

Outcome:
- editor-approved video association;
- embeddability check;
- facade-based player;
- outbound fallback;
- no autoplay.

## Iteration 3

Add ListenBrainz and Last.fm experiments.

Outcome:
- discovery signals kept separate from canonical metadata;
- optional account linking;
- consent and deletion flows.

## Iteration 4

Add Apple Music and SoundCloud only after credentials and policy review.

Outcome:
- market-aware provider links;
- optional user authorization;
- capability-specific UI.

## Iteration 5

Evaluate Spotify/Deezer/Discogs based on approved production access.

Do not build product-critical workflows until access is contractually and operationally dependable.

## Deferred

- lyrics;
- concerts;
- setlists;
- synchronized multi-provider playback;
- bulk provider-account imports.

# Provider Landscape

| Provider | Primary value | Access posture | Priority | Core dependency |
|---|---|---:|---:|---:|
| MusicBrainz | canonical identities, releases, recordings, works, credits | open API; strict identification/rate rules | P0 | Yes, but cached/imported |
| Cover Art Archive | release artwork | open HTTP API tied to MBIDs | P0 | No |
| Wikidata | knowledge graph and external identifiers | open APIs/SPARQL; query responsibly | P0 | No |
| YouTube | video links and compliant embed | API key/OAuth depending feature | P0 | No |
| Manual editorial | corrections, verification, curated links | first-party | P0 | Yes |
| ListenBrainz | listening history, statistics, discovery | public API; user token for private actions | P1 | No |
| AcoustID | fingerprint lookup | registered app key | P1 | No |
| Apple Music | catalog, deep links, MusicKit playback/user data | Apple developer credentials/tokens | P1 | No |
| SoundCloud | tracks, player, user integration | approved/self-service credentials subject to account eligibility | P1 | No |
| Last.fm | tags, listeners, scrobbles, user library | API key; commercial use requires contact | P1 | No |
| Deezer | catalog and widgets | account/app access and terms review | P2 | No |
| Spotify | metadata, playlists, user data, playback control | restricted Development Mode; production access uncertain | P2 | Never |
| Discogs | physical releases, masters, labels | API key/OAuth; terms/licensing review | P2 | No |
| Songkick | concerts and venues | paid partnership/license | P3 | No |
| setlist.fm | historical setlists | key plus commercial-use review | P3 | No |
| Lyrics vendors | licensed lyric display | paid/licensed | P3 | No |

## Provider roles

### Identity authorities

MusicBrainz is the preferred external identity backbone. Wikidata, Discogs and providers contribute cross-identifiers and assertions but never overwrite canonical records silently.

### Availability providers

YouTube, Apple Music, SoundCloud, Spotify and Deezer answer “where can the user listen?” Availability is market-specific and expires.

### Behavioral providers

ListenBrainz, Last.fm, Apple Music and approved account integrations can contribute user-authorized history, favorites or recommendations.

### Editorial sources

First-party editor decisions always retain provenance and may override provider assertions without deleting the original evidence.

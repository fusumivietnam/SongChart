# ListenBrainz

Status: P1 optional discovery/user-data provider.

## Use

- user-authorized listening history;
- public listening statistics;
- recommendation/discovery experiments.

## Rules

- User tokens are private and encrypted.
- User history is not canonical catalog truth.
- Provide disconnect and deletion workflows.
- Normalize listens through identity matching; retain original submitted metadata.
- Do not expose one user's private data to another.
- Recommendation output is a signal, not an editorial fact.

Official reference:
- https://listenbrainz.readthedocs.io/en/latest/users/api/

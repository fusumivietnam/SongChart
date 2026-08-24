# Discovery Invariants

- Discovery reads canonical catalog state but cannot mutate it.
- Discovery cannot call providers or provider ingestion/mutation services.
- Channel machine keys are immutable.
- A channel has exactly one discoverable entity type.
- Manual channels do not carry derived rules.
- Derived and hybrid channels require typed rules.
- Rules cannot contain raw SQL or arbitrary field names.
- Rule fields must be admitted by `DiscoveryFieldRegistry`.
- Publication end must be later than publication start.
- Active status alone does not bypass the publication window.
- Archived channels cannot be directly activated or paused.
- Manual items and exclusions must match the channel entity type when application handlers are introduced.
- Projections are versioned read models, not canonical entities.
- Frontend/public controllers never execute rule evaluation.

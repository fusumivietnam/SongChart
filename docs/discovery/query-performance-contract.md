# Discovery Query & Performance Contract

Stage 16.0 establishes performance boundaries for later implementation:

- no `foreach -> load()` relationship hydration on discovery result sets;
- no request-time unbounded aggregation over chart history;
- public surfaces consume bounded projections;
- rule fields and sorts must come from an indexed/approved field registry;
- explicit result limit is always enforced and cannot exceed 100;
- projection builders use batch hydration rather than per-item relation queries;
- deterministic order is mandatory for pagination, cache reproducibility and rebuild tests;
- future derived-channel queries require query-count regression coverage and PostgreSQL plan review before release.

# Discovery Projection Pipeline

Stage 16.2 materializes bounded, versioned Discovery read models outside public request execution.

Flow: canonical catalog -> cursor batches -> typed snapshots -> 16.1 rule engine -> bounded top-K -> editorial pin/exclusion merge -> immutable projection revision.

The source adapter is provider-neutral and reads only canonical Artist, Recording, Release and Collection models. Batch size is bounded to 1..1000 (default 250), channel output remains bounded by the Stage 16.0 limit of 1..100, and no public controller evaluates rules.

Manual channels resolve only configured entity IDs. Derived channels stream canonical entities in bounded batches. Hybrid channels derive first, then apply exclusions and deterministic pinned positions. Exclusions win over pins.

`discovery:rebuild {channel}` rebuilds inline; `--all` targets active channels; `--queue` dispatches unique jobs. The default scheduler enqueues active channels every fifteen minutes and can be disabled with `SONGCHART_DISCOVERY_SCHEDULE_ENABLED=false`.

Projection writes lock the channel row and allocate the next projection revision transactionally. Historical rows remain immutable read models; canonical catalog data remains authoritative.

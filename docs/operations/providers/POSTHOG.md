# PostHog

Status: recommended.

Use for:
- product analytics;
- funnels and retention;
- feature flags and experiments;
- optional web analytics;
- optional sampled session replay.

Rules:
- define events before instrumentation;
- initialize only after applicable consent;
- use opaque identities;
- mask replay inputs and sensitive UI;
- use sampling and retention controls;
- do not make application behavior depend on successful event delivery;
- do not self-host initially unless operational ownership is justified.

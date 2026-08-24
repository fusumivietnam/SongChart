# Feature Flag Rules

## Tools

- Laravel Pennant for simple application-controlled rollout gates.
- PostHog feature flags for experiments and behavior-linked analysis.
- Unleash as a future self-hosted alternative.

## Rules

- Every flag has owner, purpose, default, creation date and removal date.
- Safe local default is mandatory.
- Provider integrations have a kill switch independent of experiment flags.
- Authorization must never depend solely on a feature flag.
- Database migrations must remain safe across both flag states.
- Remove completed flags and dead branches.

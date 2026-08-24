# Discovery Projection Contract

Public request paths consume versioned Discovery projections; they do not evaluate discovery rules or perform unbounded catalog/chart aggregation.

A projection records channel revision, projection revision, rule schema version, source version, generation/expiry timestamps and a bounded list of display-ready items.

Projection items identify the canonical entity and may carry read-only display fields and metrics. Projection payloads are disposable read models and never become canonical data authority.

Cache identity should include channel identity and revision so edits naturally invalidate obsolete projections without wildcard cache deletion.

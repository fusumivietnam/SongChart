# Provider Decision Matrix

Score each candidate 0–3.

| Criterion | Weight |
|---|---:|
| Product value | 3 |
| Production access certainty | 3 |
| Legal/policy clarity | 3 |
| Stable identity support | 3 |
| Market coverage | 2 |
| Cache/persistence flexibility | 2 |
| User privacy burden | -2 |
| Vendor lock-in risk | -2 |
| Operational complexity | -2 |
| Cost | -2 |

## Gate

A provider cannot move to `approved` if any is unknown:
- commercial permission;
- credential eligibility;
- retention/cache rules;
- attribution;
- deletion/revocation;
- media handling;
- current API reference.

High product value does not override a failed compliance gate.

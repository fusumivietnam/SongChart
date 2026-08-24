# Provider Compliance Registry

## Rule

No provider integration may ship without a registry entry and contract tests.

## Registry fields

- provider name
- official developer documentation
- approved use cases
- prohibited use cases
- authentication flow
- scopes
- attribution requirements
- branding requirements
- embed/player rules
- caching/storage limits
- data retention/deletion rules
- rate limits
- market restrictions
- user-data obligations
- review/approval status
- last policy review date
- owner
- kill switch
- fallback behavior

## Integration contract

Each provider adapter implements:
- capability declaration;
- typed request/response DTOs;
- normalized error categories;
- timeout and retry policy;
- quota reporting;
- provenance;
- health check;
- deletion/revocation handling.

## Safety

- Feature flag every provider.
- Circuit breaker for repeated failures.
- Never silently substitute metadata from another provider as if it came from the requested provider.
- Raw payload retention must follow provider policy.
- Store only fields necessary for the product purpose.

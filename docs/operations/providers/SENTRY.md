# Sentry

Status: recommended.

Use for:
- backend/frontend errors;
- performance traces;
- release health;
- slow queries and N+1 investigation;
- sampled replay tied to incidents.

Rules:
- environment and release tags required;
- scrub PII and secrets;
- do not capture request bodies globally;
- control trace/replay sampling;
- exclude expected validation/business exceptions;
- alerts must describe user impact and route to an owner.

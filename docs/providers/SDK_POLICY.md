# SDK Selection Policy

Prefer direct HTTP adapters generated or validated from an official OpenAPI schema.

Use an SDK only when:
- it is official or explicitly endorsed;
- its release cadence follows the API;
- authentication behavior is transparent;
- it does not hide rate-limit headers;
- it supports timeouts and retries controlled by SongChart;
- license is compatible;
- exit cost is documented.

Every SDK wrapper must remain behind SongChart contracts. Domain/application code may not import provider SDK classes.

Lock versions and add contract fixtures before upgrading.

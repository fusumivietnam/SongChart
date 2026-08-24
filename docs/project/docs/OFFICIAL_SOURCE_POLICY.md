# Official Source Policy

Status: authoritative repository-wide engineering policy.

## Purpose

SongChartWeb follows an **official-first** implementation rule. AI agents and human contributors must use the repository authorities and the official documentation for the installed framework, dependency, provider, protocol, or database before designing custom behavior.

## Authority hierarchy

Use sources in this order:

1. Repository authorities: `AGENTS.md`, `docs/START_HERE.md`, this policy, architecture/security/stack authorities, module authorities, applicable ADRs, and the current task contract.
2. Official documentation matching the installed version.
3. Official source code, tests, changelog, upgrade guide, or schema maintained by the capability owner.
4. Primary standards such as PHP documentation, PostgreSQL documentation, RFCs, WHATWG, W3C, OAuth, or Schema.org.
5. Official issue trackers or discussions for unresolved owner-specific behavior.
6. Community sources only as discovery aids; they are not implementation authority until confirmed by an official or primary source.
7. Custom implementation only after the native-capability assessment documents the missing official capability.

Lower-ranked sources must not silently override higher-ranked authorities.

## Version matching

Before implementation, inspect the actual version authority:

- PHP dependencies: `composer.lock`, then `composer.json` constraints.
- JavaScript dependencies: `package-lock.json`, then `package.json` constraints.
- Approved stack ownership: `docs/project/stack/stack-manifest.json` and package registry.
- Runtime services such as PostgreSQL: the target runtime version and the matching official manual.

Do not rely on model memory for a version-sensitive API. If a lockfile is unavailable, record that limitation and do not claim version-specific verification.

## Native capability assessment

Every current task contract must identify:

- capability owner;
- installed version or version source;
- repository authorities consulted;
- official external sources consulted;
- native or first-party capability selected;
- custom code introduced and why the official capability is absent or insufficient;
- verification performed and verification still outstanding.

Prefer Laravel core and Laravel first-party packages for authentication, authorization, validation, queues, scheduling, cache, filesystem, HTTP, rate limiting, events, notifications, database transactions, and testing integration.

## Custom implementation threshold

Custom code is allowed only when at least one condition is documented:

- no official capability exists;
- the official capability only partially covers a SongChart-specific domain rule;
- the official solution conflicts with an approved repository constraint;
- the provider does not expose the required capability;
- adopting the official package would create a larger reviewed risk than a narrow custom boundary.

Custom code must remain narrow, reuse framework primitives, define non-goals, and have behavioral tests. A custom authentication guard, session system, provider schema, or protocol implementation requires an ADR unless explicitly authorized by an existing authority.

## Provider rule

Provider endpoints, fields, scopes, quotas, attribution, retention, media use, and policy claims must come from the provider's official documentation, schema, terms, or changelog. Never guess them. When official material is unavailable or ambiguous, preserve the uncertainty and stop the unsupported capability from becoming production behavior.

## Evidence and citations

Task contracts use the template at `docs/templates/TASK_CONTRACT_TEMPLATE.md`. URLs may be recorded in task contracts, but the decision must also name the owner, version applicability, and the specific capability supported. A link alone is not evidence.

## Freshness

Review version-sensitive and provider-policy sources at implementation time. Record the review date. Re-check sources when upgrading dependencies, activating a provider, changing authentication/security behavior, or revisiting a previously ambiguous capability.

## Conflicts and exceptions

When official documentation conflicts with installed source behavior, inspect the installed package version and official changelog, document the discrepancy, and prefer behavior proven for the installed version. Repository policy may be stricter than an upstream default. Exceptions require an ADR or an explicit authority update.

## Definition of Done

A task is not complete when:

- its current task contract omits official-source evidence;
- custom code lacks a native-capability justification;
- a version-sensitive claim is based only on memory or community material;
- a new dependency bypasses package adoption governance;
- the official-source verifier is not passing.

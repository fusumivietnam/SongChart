# Package Adoption Policy

A new package is allowed only when all items are satisfied:

- Laravel or an approved owner cannot solve the requirement reasonably.
- The capability does not already have an owner.
- The package is actively maintained and license-compatible.
- PHP/Laravel/Node constraints match the baseline.
- Security history and install scripts were reviewed.
- Static-analysis and test strategy are defined.
- Data, provider-policy and operational impact are documented.
- An exit/removal strategy exists.
- The package registry and machine manifest are updated.
- Architecture-changing adoption has an ADR.

An AI agent may recommend a package but must not install it silently. Dependency manifest and lockfile changes must be explicit review items.

## Executable admission

Every Composer dependency must have a matching entry in `package-registry.json`. Run `composer package-governance:verify`. Published vendor files must be inspected and adapted to project static-analysis rules before a stage may be packaged.

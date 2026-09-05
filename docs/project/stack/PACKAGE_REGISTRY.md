# Package Registry

Versions below are constraints from manifests. Exact resolved versions are owned by lockfiles.

| Package | Constraint | Purpose | Status | Capability owner |
|---|---:|---|---|---|
| `laravel/framework` | `^13.0` | Core framework | required | application framework |
| `laravel/fortify` | `^1.30` | Authentication and 2FA | required | authentication |
| `livewire/livewire` | `^4.0` | Server-driven interaction | approved | interactive UI |
| `laravel/pulse` | `^1.7.4` | Operational observability | required | observability |\n| `laravel/tinker` | `^3.0` | Local application console | approved | developer tooling |
| `composer/semver` | `^3.4` | Version constraint evaluation | approved | extension/version tooling |
| `larastan/larastan` | `^3.8` | Static analysis | required-dev | static analysis |
| `laravel/pint` | `^1.24` | PHP formatting | required-dev | formatting |
| `pestphp/pest` | `^4.0` | Test runner | required-dev | testing |
| `pestphp/pest-plugin-laravel` | `^4.1` | Laravel test integration | required-dev | testing |
| `fakerphp/faker` | `^1.24` | Factories and fixtures | approved-dev | test data |
| `mockery/mockery` | `^1.6` | Test doubles | approved-dev | testing |
| `nunomaduro/collision` | `^8.8` | Console test output | approved-dev | developer experience |
| `laravel/pail` | `^1.2` | Local log inspection | approved-dev | developer tooling |
| `laravel/boost` | `^2.7` | Laravel/package AI context and framework MCP tooling | required-dev | AI framework context |
| `vite` | `^7.0.0` | Frontend build | required-dev | asset build |
| `laravel-vite-plugin` | `^2.0.0` | Laravel/Vite integration | required-dev | asset build |
| `tailwindcss` | `^4.2.0` | CSS design system | required-dev | styling |
| `@tailwindcss/vite` | `^4.2.0` | Tailwind/Vite integration | required-dev | styling |
| `alpinejs` | `^3.14.9` | Local UI state | approved | local interaction |
| `axios` | `^1.11.0` | Browser HTTP utility | restricted | frontend transport |
| `concurrently` | `^9.2.1` | Local multi-process development | approved-dev | developer tooling |

## Status meanings

- `required`: application capability owner; removal requires an ADR and migration plan.
- `required-dev`: mandatory quality/build capability.
- `approved`: permitted within the documented scope.
- `approved-dev`: development/test use only.
- `restricted`: use only when an existing Laravel/Livewire/browser boundary cannot satisfy the requirement.
- `experimental`: not allowed in production paths without approval.
- `deprecated`: no new usage.
- `forbidden`: must not be installed or referenced.

No package may silently take ownership from an existing capability owner.


Machine-readable authority: `package-registry.json`. Composer package additions are invalid until both authorities and `composer package-governance:verify` agree.


Runtime database authority is PostgreSQL major 18. Database runtime versions are governed by `package-registry.json` even though PostgreSQL is not a Composer package.

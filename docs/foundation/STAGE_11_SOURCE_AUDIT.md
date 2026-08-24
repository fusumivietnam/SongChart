# Stage 11 Source Audit

Status: implemented.

## Documentation authority retained

Stage 11 does not introduce a second architecture or workflow hierarchy. The authoritative chain remains:

1. `AGENTS.md`;
2. `docs/START_HERE.md`;
3. module and phase documents;
4. applicable design or provider contract;
5. current stage task contract.

## Findings

### Aligned before Stage 11

- Laravel 13 modular monolith;
- strict Eloquent behavior outside production;
- Fortify authentication and safe 2FA view boundary;
- shared controller foundation;
- provider contracts and destination policy;
- operations-first admin dashboard read model;
- Pint, Pest and Larastan already installed.

### Corrected in Stage 11

- Admin authorization duplicated Laravel Gate behavior in a custom middleware. The route boundary now uses `can:access-admin` and a named Gate.
- Extension administration performed inline validation in the controller. Dedicated Form Requests now own each write boundary.
- Extension install and upgrade responses exposed raw exception messages. Exceptions are now reported while the UI receives a stable non-sensitive message.
- `ExtensionController` did not extend the shared application controller.
- Quality commands existed separately but no single release-oriented verification command coordinated them.

### Intentionally unchanged

- Role storage remains the existing bounded `users.role` field until a real permission matrix requires a role/permission package or tables.
- Extension managers remain application-specific because they implement SongChart package lifecycle rules rather than generic Laravel infrastructure.
- No Action layer was added merely for symmetry; current controller-to-manager delegation is already explicit and an additional abstraction would have only one implementation.

## Guardrails

The Stage 11 architecture tests protect:

- shared controller inheritance;
- Form Request usage for extension writes;
- Laravel Gate usage for admin access;
- absence of inline controller validation.

Run all project checks with:

```bash
composer verify
```

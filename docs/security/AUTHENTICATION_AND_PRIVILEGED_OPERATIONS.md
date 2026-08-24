# Authentication and Privileged Operations

SongChart uses Laravel Fortify and the session guard as the authentication authority.

## Administrator creation

The default database seeder never creates a privileged account. Create administrators interactively:

```powershell
php artisan admin:create
```

The command uses hidden password input, Laravel password rules, verified email state, an explicit privileged role, and no repository-stored password.

## Active account enforcement

Every authenticated account and administrator route uses the `active` middleware. An inactive authenticated user is logged out, the session is invalidated, and the CSRF token is regenerated.

## Privileged operations

The admin area requires a confirmed two-factor configuration. Extension mutation routes additionally require Laravel's native `password.confirm` middleware and the `manage-extensions` ability.

## Mass assignment

`User::$fillable` contains only profile/authentication fields. Roles and activation state must be assigned explicitly through privileged actions or commands.


## Authorization capability authority

Authorization uses Laravel Gates backed by `docs/project/security/authorization-contract.json`.

- `App\Enums\Capability` defines Gate names.
- `App\Support\Auth\AuthorizationMatrix` resolves the static role→capability contract.
- inactive users are denied every capability;
- privileged user-role mutation requires `manage-user-roles`;
- activation mutation requires `manage-user-activation`;
- transactional protections such as preserving the last active SuperAdmin remain business invariants in the privileged administration service.

Do not add capability methods to `UserRole`/`User` or perform role comparisons as a substitute for Gate authorization.

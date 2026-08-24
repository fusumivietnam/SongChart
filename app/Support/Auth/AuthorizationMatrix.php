<?php

declare(strict_types=1);

namespace App\Support\Auth;

use App\Enums\Capability;
use App\Enums\UserRole;
use App\Models\User;
use JsonException;
use RuntimeException;

final class AuthorizationMatrix
{
    /** @var array<string, list<string>> */
    private array $roles;

    public function __construct(string $contractPath)
    {
        if (! is_file($contractPath)) {
            throw new RuntimeException("Authorization contract is missing [{$contractPath}].");
        }

        try {
            $contract = json_decode((string) file_get_contents($contractPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Authorization contract is invalid JSON.', previous: $exception);
        }

        $roles = $contract['roles'] ?? null;
        if (! is_array($roles)) {
            throw new RuntimeException('Authorization contract roles must be an object.');
        }

        $this->roles = [];
        foreach ($roles as $role => $capabilities) {
            if (! is_string($role) || ! is_array($capabilities)) {
                throw new RuntimeException('Authorization contract role matrix is invalid.');
            }

            $this->roles[$role] = array_values(array_filter($capabilities, 'is_string'));
        }

        $this->assertComplete();
    }

    public function allows(User $user, Capability $capability): bool
    {
        if (! (bool) ($user->getAttributes()['is_active'] ?? false)) {
            return false;
        }

        $role = $user->resolvedAdminRole();

        return $role !== null && $this->roleAllows($role, $capability);
    }

    public function roleAllows(UserRole $role, Capability $capability): bool
    {
        return in_array($capability->value, $this->roles[$role->value] ?? [], true);
    }

    /** @return list<Capability> */
    public function capabilitiesFor(UserRole $role): array
    {
        return array_map(
            static fn (string $capability): Capability => Capability::from($capability),
            $this->roles[$role->value] ?? [],
        );
    }

    private function assertComplete(): void
    {
        foreach (UserRole::cases() as $role) {
            if (! array_key_exists($role->value, $this->roles)) {
                throw new RuntimeException("Authorization matrix is missing role [{$role->value}].");
            }
        }

        $knownCapabilities = array_map(
            static fn (Capability $capability): string => $capability->value,
            Capability::cases(),
        );

        foreach ($this->roles as $role => $capabilities) {
            foreach ($capabilities as $capability) {
                if (! in_array($capability, $knownCapabilities, true)) {
                    throw new RuntimeException("Authorization matrix role [{$role}] references unknown capability [{$capability}].");
                }
            }
        }
    }
}

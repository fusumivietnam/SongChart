<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Enums\Capability;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use LogicException;

final readonly class PrivilegedUserAdministration
{
    public function __construct(private PrivilegedAuditLogger $audit) {}

    public function setRole(User $target, UserRole $role, User $actor, string $rationale): void
    {
        if (! Gate::forUser($actor)->allows(Capability::ManageUserRoles->value)) {
            throw new LogicException('The actor is not authorized to change user roles.');
        }

        DB::transaction(function () use ($target, $role, $actor, $rationale): void {
            /** @var User $locked */
            $locked = User::query()->lockForUpdate()->findOrFail($target->getKey());
            $beforeRole = $locked->resolvedAdminRole();

            if (
                $beforeRole === UserRole::SuperAdmin
                && $role !== UserRole::SuperAdmin
                && $this->activeSuperAdminCountForUpdate() <= 1
            ) {
                throw new LogicException('The last active super administrator cannot be demoted.');
            }

            $locked->forceFill(['role' => $role])->save();
            $locked->refresh();

            $this->audit->record(
                event: 'user.role-changed',
                description: 'User role changed.',
                subject: $locked,
                actor: $actor,
                before: ['role' => $beforeRole?->value],
                after: ['role' => $locked->resolvedAdminRole()?->value],
                rationale: $rationale,
            );
        });
    }

    public function setActive(User $target, bool $active, User $actor, string $rationale): void
    {
        if (! Gate::forUser($actor)->allows(Capability::ManageUserActivation->value)) {
            throw new LogicException('The actor is not authorized to change account activation state.');
        }

        if ((string) $target->getKey() === (string) $actor->getKey() && ! $active) {
            throw new LogicException('An administrator cannot deactivate their own account.');
        }

        DB::transaction(function () use ($target, $active, $actor, $rationale): void {
            /** @var User $locked */
            $locked = User::query()->lockForUpdate()->findOrFail($target->getKey());
            $before = (bool) $locked->is_active;

            if (
                $before
                && ! $active
                && $locked->resolvedAdminRole() === UserRole::SuperAdmin
                && $this->activeSuperAdminCountForUpdate() <= 1
            ) {
                throw new LogicException('The last active super administrator cannot be deactivated.');
            }

            $locked->forceFill(['is_active' => $active])->save();
            $locked->refresh();

            $this->audit->record(
                event: $active ? 'user.activated' : 'user.deactivated',
                description: $active ? 'User account activated.' : 'User account deactivated.',
                subject: $locked,
                actor: $actor,
                before: ['is_active' => $before],
                after: ['is_active' => (bool) $locked->is_active],
                rationale: $rationale,
            );
        });
    }

    private function activeSuperAdminCountForUpdate(): int
    {
        $lockedSuperAdmins = User::query()
            ->where('role', UserRole::SuperAdmin->value)
            ->where('is_active', true)
            ->lockForUpdate()
            ->get(['id']);

        return count($lockedSuperAdmins);
    }
}

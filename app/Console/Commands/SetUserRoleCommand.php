<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Admin\PrivilegedUserAdministration;
use Illuminate\Console\Command;
use LogicException;

final class SetUserRoleCommand extends Command
{
    protected $signature = 'admin:user:set-role
        {email : Target user email}
        {role : New role}
        {--actor= : Existing super administrator email recorded as the audit actor}
        {--reason= : Required business rationale, minimum 10 characters}
        {--yes : Skip interactive confirmation}';

    protected $description = 'Change a user role through an audited privileged operation.';

    public function handle(PrivilegedUserAdministration $administration): int
    {
        $target = User::query()->where('email', mb_strtolower(trim((string) $this->argument('email'))))->first();
        $actor = User::query()->where('email', mb_strtolower(trim((string) $this->option('actor'))))->first();
        $role = UserRole::tryFrom((string) $this->argument('role'));
        $reason = trim((string) $this->option('reason'));

        if ($target === null || $actor === null || $role === null || mb_strlen($reason) < 10) {
            $this->error('Target, actor, valid role, and a rationale of at least 10 characters are required.');

            return self::FAILURE;
        }

        if (! $this->option('yes') && ! $this->confirm("Change {$target->email} role to {$role->value}?")) {
            $this->warn('No changes were made.');

            return self::SUCCESS;
        }

        try {
            $administration->setRole($target, $role, $actor, $reason);
        } catch (LogicException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('User role changed and privileged audit recorded.');

        return self::SUCCESS;
    }
}

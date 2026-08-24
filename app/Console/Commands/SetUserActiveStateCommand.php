<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Admin\PrivilegedUserAdministration;
use Illuminate\Console\Command;
use LogicException;

final class SetUserActiveStateCommand extends Command
{
    protected $signature = 'admin:user:set-active
        {email : Target user email}
        {state : active|inactive}
        {--actor= : Existing operations administrator email recorded as the audit actor}
        {--reason= : Required business rationale, minimum 10 characters}
        {--yes : Skip interactive confirmation}';

    protected $description = 'Activate or deactivate a user account through an audited privileged operation.';

    public function handle(PrivilegedUserAdministration $administration): int
    {
        $target = User::query()->where('email', mb_strtolower(trim((string) $this->argument('email'))))->first();
        $actor = User::query()->where('email', mb_strtolower(trim((string) $this->option('actor'))))->first();
        $state = (string) $this->argument('state');
        $reason = trim((string) $this->option('reason'));

        if ($target === null || $actor === null || ! in_array($state, ['active', 'inactive'], true) || mb_strlen($reason) < 10) {
            $this->error('Target, actor, state active|inactive, and a rationale of at least 10 characters are required.');

            return self::FAILURE;
        }

        if (! $this->option('yes') && ! $this->confirm("Set {$target->email} to {$state}?")) {
            $this->warn('No changes were made.');

            return self::SUCCESS;
        }

        try {
            $administration->setActive($target, $state === 'active', $actor, $reason);
        } catch (LogicException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('User activation state changed and privileged audit recorded.');

        return self::SUCCESS;
    }
}

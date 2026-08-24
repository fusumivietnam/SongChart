<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Capability;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\Auth\AuthorizationMatrix;
use Illuminate\Console\Command;

final class EnsureLocalAdminCommand extends Command
{
    protected $signature = 'admin:ensure-local
        {email? : Existing local administrator email address}
        {--name=SongChart Admin : Display name used only when a new administrator must be created}
        {--role=super_admin : Privileged role to ensure}';

    protected $description = 'Ensure a local administrator exists without resetting password or two-factor secrets.';

    public function handle(AuthorizationMatrix $authorization): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Local administrator bootstrap is disabled outside local/testing environments.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email'))));
        $role = UserRole::tryFrom((string) $this->option('role'));

        if ($role === null || ! $authorization->roleAllows($role, Capability::AccessAdmin)) {
            $this->error('The selected role is not a privileged administrator role.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        if ($user === null) {
            $this->info('No matching local account exists; launching the administrator creator.');

            return $this->call('admin:create', [
                'email' => $email,
                '--name' => (string) $this->option('name'),
                '--role' => $role->value,
            ]);
        }

        $user->forceFill([
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info(sprintf('Local administrator %s is ready; password and two-factor state were preserved.', $email));

        return self::SUCCESS;
    }
}

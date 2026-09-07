<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Capability;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\Auth\AuthorizationMatrix;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class EnsureLocalAdminCommand extends Command
{
    protected $signature = 'admin:ensure-local
        {email? : Existing local administrator email address}
        {--name=SongChart Admin : Display name used only when a new administrator must be created}
        {--role=super_admin : Privileged role to ensure}';

    protected $description = 'Ensure a development administrator exists without resetting existing password or two-factor secrets.';

    public function handle(AuthorizationMatrix $authorization): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Local administrator bootstrap is disabled outside local/testing environments.');

            return self::FAILURE;
        }

        $configuredEmail = trim((string) getenv('SONGCHART_DEV_ADMIN_EMAIL'));
        $email = mb_strtolower(trim((string) ($this->argument('email') ?: $configuredEmail ?: $this->ask('Email'))));
        $role = UserRole::tryFrom((string) $this->option('role'));

        if ($role === null || ! $authorization->roleAllows($role, Capability::AccessAdmin)) {
            $this->error('The selected role is not a privileged administrator role.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        if ($user === null) {
            $configuredPassword = (string) getenv('SONGCHART_DEV_ADMIN_PASSWORD');

            if ($configuredPassword === '') {
                $this->info('No matching local account exists and no development bootstrap password is configured; launching the interactive administrator creator.');

                return $this->call('admin:create', [
                    'email' => $email,
                    '--name' => (string) $this->option('name'),
                    '--role' => $role->value,
                ]);
            }

            $name = trim((string) $this->option('name'));
            $validator = Validator::make([
                'email' => $email,
                'name' => $name,
                'password' => $configuredPassword,
                'password_confirmation' => $configuredPassword,
            ], [
                'email' => ['required', 'email:rfc', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $this->error($message);
                }

                return self::FAILURE;
            }

            $user = new User;
            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($configuredPassword),
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();

            $this->info(sprintf('Development administrator %s was recreated from configured secret-backed bootstrap credentials.', $email));

            return self::SUCCESS;
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

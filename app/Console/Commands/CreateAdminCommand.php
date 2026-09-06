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

final class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
        {email? : Administrator email address}
        {--name= : Administrator display name}
        {--role=super_admin : Privileged role to assign}
        {--if-missing : Succeed without changing credentials when the account already exists}';

    protected $description = 'Create a verified privileged SongChart administrator without a default password.';

    public function handle(AuthorizationMatrix $authorization): int
    {
        $email = mb_strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email'))));
        $name = trim((string) ($this->option('name') ?: $this->ask('Name', 'SongChart Admin')));
        $role = UserRole::tryFrom((string) $this->option('role'));

        if ($role === null || ! $authorization->roleAllows($role, Capability::AccessAdmin)) {
            $this->error('The selected role is not a privileged administrator role.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            if ((bool) $this->option('if-missing')) {
                $this->info(sprintf('Administrator %s already exists; credentials were preserved.', $email));

                return self::SUCCESS;
            }

            $this->error('A user with this email already exists.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $confirmation,
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

        if (! $this->confirm(sprintf('Create verified %s account for %s?', $role->value, $email))) {
            $this->warn('Administrator creation cancelled.');

            return self::FAILURE;
        }

        $user = new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info(sprintf('Administrator %s created.', $email));

        return self::SUCCESS;
    }
}

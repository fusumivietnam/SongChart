<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /** @param array<string, string> $input */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ])->validate();

        $user = User::query()->create([
            'name' => trim($input['name']),
            'email' => mb_strtolower(trim($input['email'])),
            'password' => $input['password'],
        ]);

        $user->forceFill([
            'role' => UserRole::User,
            'is_active' => true,
        ])->save();

        return $user;
    }
}

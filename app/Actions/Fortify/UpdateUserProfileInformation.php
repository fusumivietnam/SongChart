<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

final class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /** @param array<string, string> $input */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ])->validateWithBag('updateProfileInformation');

        $email = mb_strtolower(trim($input['email']));

        if ($email !== $user->email) {
            $user->forceFill([
                'name' => trim($input['name']),
                'email' => $email,
                'email_verified_at' => null,
            ])->save();
            $user->sendEmailVerificationNotification();

            return;
        }

        $user->forceFill(['name' => trim($input['name']), 'email' => $email])->save();
    }
}

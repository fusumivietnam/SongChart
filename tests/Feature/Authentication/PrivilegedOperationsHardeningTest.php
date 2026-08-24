<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('logs out an authenticated user after the account is deactivated', function (): void {
    $user = User::factory()->create([
        'is_active' => false,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/account')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('requires confirmed two factor authentication for the admin area', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect(route('account.security'));
});

it('creates no default administrator through the database seeder', function (): void {
    $this->seed();

    expect(User::query()->where('email', 'admin@songchart.test')->exists())->toBeFalse();
});

it('creates a privileged administrator through the interactive command', function (): void {
    $this->artisan('admin:create', [
        'email' => 'owner@example.test',
        '--name' => 'Owner',
        '--role' => UserRole::SuperAdmin->value,
    ])
        ->expectsQuestion('Password', 'StrongPassword!123')
        ->expectsQuestion('Confirm password', 'StrongPassword!123')
        ->expectsConfirmation('Create verified super_admin account for owner@example.test?', 'yes')
        ->assertSuccessful();

    $user = User::query()->where('email', 'owner@example.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::SuperAdmin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('StrongPassword!123', $user->password))->toBeTrue();
});

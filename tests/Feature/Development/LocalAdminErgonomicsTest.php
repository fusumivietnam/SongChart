<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    putenv('SONGCHART_DEV_ADMIN_EMAIL');
    putenv('SONGCHART_DEV_ADMIN_PASSWORD');
});

it('preserves an existing local administrator password and two factor state', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create([
        'email' => 'owner@example.test',
        'role' => UserRole::Reviewer,
        'is_active' => false,
        'email_verified_at' => null,
        'two_factor_secret' => 'existing-secret',
        'two_factor_recovery_codes' => 'existing-recovery-codes',
        'two_factor_confirmed_at' => now()->subDay(),
    ]);

    $password = $user->getRawOriginal('password');
    $secret = $user->getRawOriginal('two_factor_secret');
    $recoveryCodes = $user->getRawOriginal('two_factor_recovery_codes');
    $confirmedAt = $user->getRawOriginal('two_factor_confirmed_at');

    $this->artisan('admin:ensure-local', [
        'email' => 'owner@example.test',
        '--role' => UserRole::SuperAdmin->value,
    ])->assertSuccessful();

    $user->refresh();

    expect($user->id)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::SuperAdmin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->getRawOriginal('password'))->toBe($password)
        ->and($user->getRawOriginal('two_factor_secret'))->toBe($secret)
        ->and($user->getRawOriginal('two_factor_recovery_codes'))->toBe($recoveryCodes)
        ->and($user->getRawOriginal('two_factor_confirmed_at'))->toBe($confirmedAt);
});

it('recreates a missing development super admin from secret-backed bootstrap credentials', function (): void {
    /** @var TestCase $this */
    app()->detectEnvironment(static fn (): string => 'local');
    putenv('SONGCHART_DEV_ADMIN_EMAIL=admin@songchart.local');
    putenv('SONGCHART_DEV_ADMIN_PASSWORD=Correct-Horse-Battery-Staple-2026!');

    $this->artisan('admin:ensure-local', [
        '--name' => 'SongChart Admin',
        '--role' => UserRole::SuperAdmin->value,
    ])->assertSuccessful();

    $user = User::query()->where('email', 'admin@songchart.local')->firstOrFail();

    expect($user->role)->toBe(UserRole::SuperAdmin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('Correct-Horse-Battery-Staple-2026!', $user->password))->toBeTrue();
});

it('allows local development to bypass admin two factor confirmation when explicitly disabled', function (): void {
    /** @var TestCase $this */
    app()->detectEnvironment(static fn (): string => 'local');
    config()->set('songchart.security.admin_2fa_mode', 'disabled');

    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user)->get('/admin')->assertOk();
});

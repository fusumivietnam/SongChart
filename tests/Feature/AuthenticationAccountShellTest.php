<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('renders the complete public authentication entry points', function (): void {
    $this->get('/login')->assertOk()->assertSee('Đăng nhập')->assertSee('Tạo tài khoản');
    $this->get('/register')->assertOk()->assertSee('Tạo tài khoản')->assertSee('register-email', false);
    $this->get('/forgot-password')->assertOk()->assertSee('Khôi phục mật khẩu');
});

it('registers public accounts with the least privileged role', function (): void {
    $response = $this->post('/register', [
        'name' => 'Public Listener',
        'email' => 'listener@example.test',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
        'terms' => '1',
    ]);

    $response->assertRedirect('/account');
    $user = User::query()->where('email', 'listener@example.test')->firstOrFail();
    expect($user->role)->toBe(UserRole::User)->and($user->is_active)->toBeTrue();
});

it('does not authenticate inactive accounts', function (): void {
    User::factory()->create([
        'email' => 'inactive@example.test',
        'password' => Hash::make('password'),
        'is_active' => false,
    ]);

    $this->post('/login', ['email' => 'inactive@example.test', 'password' => 'password'])
        ->assertSessionHasErrors();

    $this->assertGuest();
});

it('redirects authenticated users to account rather than admin', function (): void {
    User::factory()->create([
        'email' => 'member@example.test',
        'password' => Hash::make('password'),
    ]);

    $this->post('/login', ['email' => 'member@example.test', 'password' => 'password'])
        ->assertRedirect('/account');
});

it('protects the account shell with authentication and email verification', function (): void {
    $this->get('/account')->assertRedirect('/login');

    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified)->get('/account')->assertRedirect(route('verification.notice'));

    $verified = User::factory()->create();
    $this->actingAs($verified)->get('/account')
        ->assertOk()
        ->assertSee('data-account-section="overview"', false)
        ->assertSee($verified->email);
});

it('renders profile and security account sections with stable semantic markers', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/account/profile')
        ->assertOk()
        ->assertSee('data-account-section="profile"', false)
        ->assertSee('profile-email', false);

    $this->actingAs($user)->get('/account/security')
        ->assertOk()
        ->assertSee('data-account-section="security"', false)
        ->assertSee('data-security-section="password"', false)
        ->assertSee('data-security-section="two-factor"', false);
});

it('treats missing two factor attributes as an unconfirmed state', function (): void {
    $created = User::factory()->create();
    $partiallyHydrated = User::query()->select(['id', 'email'])->findOrFail($created->id);

    expect($partiallyHydrated->hasConfirmedTwoFactorAuthentication())->toBeFalse();
});

it('keeps two factor state helpers safe for partially hydrated users', function (): void {
    $created = User::factory()->create();

    $user = User::query()->select(['id', 'email'])->findOrFail($created->id);

    expect($user->hasTwoFactorAuthenticationConfigured())->toBeFalse()
        ->and($user->hasPendingTwoFactorAuthentication())->toBeFalse()
        ->and($user->hasConfirmedTwoFactorAuthentication())->toBeFalse();
});

it('does not access two factor persistence attributes directly from account views', function (): void {
    $view = file_get_contents(resource_path('views/account/security.blade.php'));

    expect($view)->not->toContain('->two_factor_secret')
        ->and($view)->not->toContain('->two_factor_confirmed_at');
});

test('user roles expose stable presentation labels', function (): void {
    expect(UserRole::SuperAdmin->label())->toBe('Super Admin')
        ->and(UserRole::ProviderManager->label())->toBe('Provider Manager');
});

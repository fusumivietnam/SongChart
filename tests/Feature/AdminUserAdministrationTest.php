<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

function stage184UserAdministrator(UserRole $role = UserRole::SuperAdmin): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-18-4-user-admin-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
}

it('allows a super administrator to change a user role through the admin surface', function (): void {
    $actor = stage184UserAdministrator();
    $target = User::factory()->create([
        'role' => UserRole::User,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($actor)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->patch(route('admin.users.role.update', $target), [
            'role' => UserRole::Reviewer->value,
            'rationale' => 'Reviewer access approved for catalog operations.',
        ])
        ->assertRedirect();

    expect($target->fresh()->role)->toBe(UserRole::Reviewer)
        ->and(Activity::query()->where('event', 'user.role-changed')->where('subject_id', $target->getKey())->exists())->toBeTrue();
});

it('allows a system operator to change account activation through the admin surface', function (): void {
    $actor = stage184UserAdministrator(UserRole::SystemOperator);
    $target = User::factory()->create([
        'role' => UserRole::User,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($actor)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->patch(route('admin.users.active.update', $target), [
            'active' => false,
            'rationale' => 'Account suspended for operational review.',
        ])
        ->assertRedirect();

    expect($target->fresh()->is_active)->toBeFalse()
        ->and(Activity::query()->where('event', 'user.deactivated')->where('subject_id', $target->getKey())->exists())->toBeTrue();
});

it('keeps role mutation unavailable to a system operator', function (): void {
    $actor = stage184UserAdministrator(UserRole::SystemOperator);
    $target = User::factory()->create([
        'role' => UserRole::User,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($actor)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->patch(route('admin.users.role.update', $target), [
            'role' => UserRole::Reviewer->value,
            'rationale' => 'This request must remain unauthorized.',
        ])
        ->assertForbidden();

    expect($target->fresh()->role)->toBe(UserRole::User);
});

it('renders only the user operations allowed by the actor capabilities', function (): void {
    $target = User::factory()->create([
        'role' => UserRole::User,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs(stage184UserAdministrator())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Cập nhật vai trò')
        ->assertSee('Vô hiệu hóa');

    $this->actingAs(stage184UserAdministrator(UserRole::SystemOperator))
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertDontSee('Cập nhật vai trò')
        ->assertSee('Vô hiệu hóa');
});

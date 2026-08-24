<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stage162Admin(): User
{
    return User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-16-2-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
}

it('protects every admin information architecture route', function (string $route): void {
    $this->get(route($route))->assertRedirect(route('login'));
})->with([
    'admin.catalog.index',
    'admin.providers.index',
    'admin.imports.index',
    'admin.quarantine.index',
    'admin.identity-conflicts.index',
    'admin.users.index',
    'admin.system.index',
]);

it('renders every read only operations surface for an administrator', function (string $route, string $marker): void {
    $this->actingAs(stage162Admin())
        ->get(route($route))
        ->assertOk()
        ->assertSee('data-admin-section="'.$marker.'"', false);
})->with([
    ['admin.catalog.index', 'catalog'],
    ['admin.providers.index', 'providers'],
    ['admin.imports.index', 'imports'],
    ['admin.quarantine.index', 'quarantine'],
    ['admin.identity-conflicts.index', 'identity-conflicts'],
    ['admin.users.index', 'users'],
    ['admin.system.index', 'system-health'],
]);

it('publishes only navigable stage 16.2 admin entries', function (): void {
    $this->actingAs(stage162Admin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.catalog.index'), false)
        ->assertSee(route('admin.providers.index'), false)
        ->assertSee(route('admin.imports.index'), false)
        ->assertSee(route('admin.quarantine.index'), false)
        ->assertSee(route('admin.identity-conflicts.index'), false)
        ->assertSee(route('admin.users.index'), false)
        ->assertSee(route('admin.system.index'), false);
});

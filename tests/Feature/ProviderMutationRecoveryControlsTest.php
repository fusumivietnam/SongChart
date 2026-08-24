<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\Providers\ProviderOperationAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('requires provider management authorization for mutations', function (): void {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create([
        'role' => UserRole::Reviewer,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-168-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
    $provider = Provider::query()->create([
        'slug' => 'test-provider',
        'name' => 'Test',
        'category' => 'metadata',
        'status' => 'approved',
        'is_enabled' => false,
    ]);

    $this->actingAs($user)
        ->post(route('admin.providers.mutate', $provider), [
            'action' => 'enable',
            'rationale' => 'Required operational rationale.',
            'idempotency_key' => 'auth-test',
        ])
        ->assertForbidden();
});

it('records an idempotent provider mutation audit', function (): void {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-168-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
    $provider = Provider::query()->create([
        'slug' => 'audit-provider',
        'name' => 'Audit',
        'category' => 'metadata',
        'status' => 'approved',
        'is_enabled' => false,
    ]);
    $payload = [
        'action' => 'enable',
        'rationale' => 'Enable provider after operational review.',
        'idempotency_key' => 'provider-enable-1',
    ];

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.mutate', $provider), $payload)
        ->assertRedirect();
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.mutate', $provider), $payload)
        ->assertRedirect();

    expect($provider->refresh()->is_enabled)->toBeTrue()
        ->and(ProviderOperationAudit::query()->where('idempotency_key', 'provider-enable-1')->count())->toBe(1);
});

<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

function stage1681Admin(UserRole $role = UserRole::SuperAdmin): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-1681-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
}

it('presents the admin dashboard as an attention center', function (): void {
    /** @var TestCase $this */
    $this->actingAs(stage1681Admin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-admin-dashboard="attention-first"', false)
        ->assertSee('Cần xử lý')
        ->assertSee('Dữ liệu cần rà soát')
        ->assertSee('Chi tiết kỹ thuật');
});

it('uses operational language instead of provider ledger terminology on primary surfaces', function (): void {
    /** @var TestCase $this */
    $provider = Provider::query()->create([
        'slug' => 'stage1681-source',
        'name' => 'Stage 1681 Source',
        'category' => 'metadata',
        'status' => 'approved',
        'is_enabled' => true,
    ]);
    $run = ProviderImportRun::query()->create([
        'provider_id' => $provider->id,
        'operation' => 'artist-import',
        'status' => 'failed',
        'configuration_hash' => str_repeat('c', 64),
        'configuration' => [],
        'statistics' => [],
        'attempts' => 1,
    ]);
    $admin = stage1681Admin();

    $this->actingAs($admin)->get(route('admin.providers.index'))
        ->assertOk()->assertSee('Nguồn dữ liệu')->assertSee('Stage 1681 Source');
    $this->actingAs($admin)->get(route('admin.imports.show', $run))
        ->assertOk()->assertSee('Khôi phục tác vụ')->assertSee('Chi tiết kỹ thuật');
});

it('makes navigation role aware without changing route authorization contracts', function (): void {
    /** @var TestCase $this */
    $editor = stage1681Admin(UserRole::Editor);
    $providerManager = stage1681Admin(UserRole::ProviderManager);

    $this->actingAs($editor)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-admin-nav="identity-conflicts"', false)
        ->assertDontSee('data-admin-nav="imports"', false);

    $this->actingAs($providerManager)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-admin-nav="providers"', false)
        ->assertSee('data-admin-nav="imports"', false);
});

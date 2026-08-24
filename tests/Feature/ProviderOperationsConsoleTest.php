<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

function stage166Admin(): User
{
    return User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-16-6-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
}

it('protects provider and import detail routes', function (): void {
    /** @var TestCase $this */
    $provider = Provider::query()->create([
        'slug' => 'stage166',
        'name' => 'Stage 166',
        'category' => 'metadata',
        'status' => 'sandbox',
        'is_enabled' => true,
    ]);
    $run = ProviderImportRun::query()->create([
        'provider_id' => $provider->id,
        'operation' => 'artist-import',
        'status' => 'queued',
        'configuration_hash' => str_repeat('a', 64),
        'configuration' => [],
        'statistics' => [],
        'attempts' => 0,
    ]);

    $this->get(route('admin.providers.show', $provider))->assertRedirect(route('login'));
    $this->get(route('admin.imports.show', $run))->assertRedirect(route('login'));
});

it('renders provider operational detail for an administrator', function (): void {
    /** @var TestCase $this */
    $provider = Provider::query()->create([
        'slug' => 'stage166-detail',
        'name' => 'Stage 166 Detail',
        'category' => 'metadata',
        'status' => 'sandbox',
        'is_enabled' => true,
    ]);
    $provider->capabilities()->create([
        'capability' => 'artist-lookup',
        'status' => 'available',
        'requires_user_consent' => false,
        'market_dependent' => false,
    ]);

    $this->actingAs(stage166Admin())
        ->get(route('admin.providers.show', $provider))
        ->assertOk()
        ->assertSee('data-admin-section="provider-detail"', false)
        ->assertSee('Stage 166 Detail');
});

it('renders import ledger detail for an administrator', function (): void {
    /** @var TestCase $this */
    $provider = Provider::query()->create([
        'slug' => 'stage166-import',
        'name' => 'Stage 166 Import',
        'category' => 'metadata',
        'status' => 'sandbox',
        'is_enabled' => true,
    ]);
    $run = ProviderImportRun::query()->create([
        'provider_id' => $provider->id,
        'operation' => 'artist-import',
        'status' => 'queued',
        'configuration_hash' => str_repeat('b', 64),
        'configuration' => [],
        'statistics' => [],
        'attempts' => 0,
    ]);

    $this->actingAs(stage166Admin())
        ->get(route('admin.imports.show', $run))
        ->assertOk()
        ->assertSee('data-admin-section="import-detail"', false)
        ->assertSee($run->id);
});

it('keeps provider operation read surfaces read only and exposes only governed mutations', function (): void {
    $routes = app('router')->getRoutes();

    foreach ([
        'admin.providers.index',
        'admin.providers.show',
        'admin.imports.index',
        'admin.imports.show',
        'admin.quarantine.index',
    ] as $routeName) {
        $route = $routes->getByName($routeName);
        assert($route !== null);
        expect($route->methods())->toBe(['GET', 'HEAD']);
    }

    $providerMutation = $routes->getByName('admin.providers.mutate');
    $importRecovery = $routes->getByName('admin.imports.recover');
    assert($providerMutation !== null);
    assert($importRecovery !== null);

    expect($providerMutation->methods())->toBe(['POST'])
        ->and($importRecovery->methods())->toBe(['POST']);
});

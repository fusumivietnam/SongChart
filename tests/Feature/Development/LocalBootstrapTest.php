<?php

declare(strict_types=1);

use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the local readiness page with stable markers', function (): void {
    $this->get('/development/status')
        ->assertOk()
        ->assertSee('data-development-status', false)
        ->assertSee('data-check="environment"', false)
        ->assertSee(route('login'))
        ->assertSee(route('admin.dashboard'));
});

it('runs the local bootstrap without creating a default administrator', function (): void {
    $this->artisan('songchart:setup-local', ['--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Important local URLs');

    $this->assertDatabaseCount('users', 0);
});

it('seeds deterministic demo catalog data only when requested', function (): void {
    $this->artisan('songchart:setup-local', ['--force' => true, '--demo' => true])
        ->assertSuccessful();

    $this->assertDatabaseHas('artists', ['slug' => 'radiohead']);
});

it('renders enum-backed latest import state without reporting the database unavailable', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => 'approved',
        'is_enabled' => true,
    ]);
    ProviderImportRun::query()->create([
        'provider_id' => $provider->id,
        'operation' => 'artist-lookup',
        'status' => ProviderImportRunStatus::Completed,
        'requested_by_type' => 'development',
        'requested_by_id' => null,
        'configuration_hash' => str_repeat('a', 64),
        'configuration' => [],
        'statistics' => [],
        'attempts' => 1,
    ]);

    $this->get('/development/status')
        ->assertOk()
        ->assertSee('completed')
        ->assertDontSee('database unavailable');
});

<?php

declare(strict_types=1);

use App\Actions\Providers\DispatchProviderHealthChecks;
use App\Contracts\Providers\ProviderAdapter;
use App\Contracts\Providers\ProviderHealth;
use App\Domain\Providers\Enums\ProviderSyncStatus;
use App\Jobs\Providers\CheckProviderHealth;
use App\Models\Provider;
use App\Models\ProviderSyncRun;
use App\Support\Providers\ProviderAdapterRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('queues health checks only for enabled providers', function (): void {
    Bus::fake();

    $enabled = Provider::query()->create([
        'slug' => 'enabled-provider',
        'name' => 'Enabled Provider',
        'category' => 'music',
        'status' => 'approved',
        'is_enabled' => true,
    ]);

    Provider::query()->create([
        'slug' => 'disabled-provider',
        'name' => 'Disabled Provider',
        'category' => 'music',
        'status' => 'approved',
        'is_enabled' => false,
    ]);

    $count = app(DispatchProviderHealthChecks::class)->handle();

    expect($count)->toBe(1)
        ->and(ProviderSyncRun::query()->count())->toBe(1)
        ->and(ProviderSyncRun::query()->firstOrFail()->provider_id)->toBe($enabled->getKey());

    Bus::assertDispatched(CheckProviderHealth::class, fn (CheckProviderHealth $job): bool => $job->providerId === $enabled->getKey());
});

it('does not create duplicate recent health runs unless forced', function (): void {
    Bus::fake();

    $provider = Provider::query()->create([
        'slug' => 'deduplicated-provider',
        'name' => 'Deduplicated Provider',
        'category' => 'music',
        'status' => 'approved',
        'is_enabled' => true,
    ]);

    ProviderSyncRun::query()->create([
        'provider_id' => $provider->getKey(),
        'operation' => 'health-check',
        'status' => 'queued',
        'started_at' => now(),
    ]);

    expect(app(DispatchProviderHealthChecks::class)->handle())->toBe(0)
        ->and(app(DispatchProviderHealthChecks::class)->handle(force: true))->toBe(1);
});

it('records successful adapter health checks', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'healthy-provider',
        'name' => 'Healthy Provider',
        'category' => 'music',
        'status' => 'approved',
        'is_enabled' => true,
    ]);

    $run = ProviderSyncRun::query()->create([
        'provider_id' => $provider->getKey(),
        'operation' => 'health-check',
        'status' => 'queued',
        'started_at' => now(),
    ]);

    $adapter = new class implements ProviderAdapter
    {
        public function providerSlug(): string
        {
            return 'healthy-provider';
        }

        public function capabilities(): array
        {
            return ['catalog.lookup'];
        }

        public function healthCheck(): ProviderHealth
        {
            return new ProviderHealth(true, 'Provider responded normally.', 42);
        }
    };

    $job = new CheckProviderHealth((string) $provider->getKey(), (string) $run->getKey());
    $job->handle(new ProviderAdapterRegistry([$adapter]));

    $run->refresh();

    expect($run->status)->toBe(ProviderSyncStatus::Succeeded)
        ->and($run->processed_count)->toBe(1)
        ->and($run->failed_count)->toBe(0)
        ->and($run->error_summary)->toBe('Provider responded normally.')
        ->and($run->finished_at)->not->toBeNull();
});

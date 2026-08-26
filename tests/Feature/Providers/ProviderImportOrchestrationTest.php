<?php

declare(strict_types=1);

use App\Contracts\Providers\Catalog\ProviderCatalogAdapter;
use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPage;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Catalog\Enums\ProviderCatalogCapability;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use App\Jobs\Providers\Ingestion\FetchProviderImportPage;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Support\Providers\Catalog\InMemoryProviderCatalogAdapterRegistry;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function orchestrationAdapter(): ProviderCatalogAdapter
{
    return new class implements ProviderCatalogAdapter
    {
        public function providerSlug(): string
        {
            return 'fixture';
        }

        public function capabilities(): array
        {
            return [ProviderCatalogCapability::ArtistLookup];
        }

        public function fetchPage(ProviderImportContext $context, ?string $cursor = null): ProviderPage
        {
            return new ProviderPage([
                new ProviderPayload('fixture', EntityType::Artist, 'artist-1', ['name' => 'Fixture Artist'], new DateTimeImmutable('2026-08-04T00:00:00+00:00')),
            ], null, null, true);
        }

        public function normalize(ProviderPayload $payload): NormalizedProviderEntity
        {
            return new NormalizedProviderEntity(
                'fixture',
                $payload->entityType,
                $payload->externalId,
                new NormalizedArtist(
                    ProviderField::provided($payload->data['name']),
                    ProviderField::missing(),
                    ProviderField::missing(),
                    ProviderField::missing(),
                    ProviderField::missing(),
                    ProviderField::missing(),
                    ProviderField::missing(),
                ),
                [],
                [],
                'fixture-v1',
            );
        }
    };
}

it('starts an import run and queues the first page', function (): void {
    Queue::fake();
    $provider = Provider::query()->create(['slug' => 'fixture', 'name' => 'Fixture', 'category' => 'metadata', 'status' => ProviderStatus::Approved, 'is_enabled' => true]);

    $run = app(ProviderImportOrchestrator::class)->start($provider, EntityType::Artist, 'artist-import', 'artist-1');

    expect($run->status)->toBe(ProviderImportRunStatus::Queued)
        ->and($run->configuration_hash)->toHaveLength(64);
    Queue::assertPushed(FetchProviderImportPage::class, fn (FetchProviderImportPage $job): bool => $job->runId === $run->getKey());
});

it('fetches normalizes checkpoints and finalizes a page', function (): void {
    Queue::fake();
    $adapter = orchestrationAdapter();
    app()->instance(ProviderCatalogAdapterRegistry::class, new InMemoryProviderCatalogAdapterRegistry([$adapter]));
    $provider = Provider::query()->create(['slug' => 'fixture', 'name' => 'Fixture', 'category' => 'metadata', 'status' => ProviderStatus::Approved, 'is_enabled' => true]);
    $run = app(ProviderImportOrchestrator::class)->start($provider, EntityType::Artist, 'artist-import', 'artist-1');

    (new FetchProviderImportPage((string) $run->getKey()))->handle(
        app(ProviderCatalogAdapterRegistry::class),
        app(ProviderImportOrchestrator::class),
        app(ProviderRuntimeConfiguration::class),
    );

    $run->refresh();
    expect($run->payloads()->count())->toBe(1)
        ->and($run->items()->count())->toBe(1)
        ->and($run->checkpoints()->where('checkpoint_key', 'page')->exists())->toBeTrue();
});

it('resumes from the committed page checkpoint and rejects terminal resumes', function (): void {
    Queue::fake();
    $provider = Provider::query()->create(['slug' => 'fixture', 'name' => 'Fixture', 'category' => 'metadata', 'status' => ProviderStatus::Approved, 'is_enabled' => true]);
    $run = ProviderImportRun::query()->create([
        'provider_id' => $provider->getKey(), 'operation' => 'artist-import', 'status' => ProviderImportRunStatus::Paused,
        'configuration_hash' => str_repeat('a', 64), 'configuration' => ['entity_type' => 'artist', 'page_size' => 50, 'options' => []],
    ]);
    $run->checkpoints()->create(['checkpoint_key' => 'page', 'cursor' => 'cursor-2', 'committed_at' => now()]);

    app(ProviderImportOrchestrator::class)->resume($run);
    Queue::assertPushed(FetchProviderImportPage::class, fn (FetchProviderImportPage $job): bool => $job->cursor === 'cursor-2');

    $run->forceFill(['status' => ProviderImportRunStatus::Completed])->save();
    expect(fn () => app(ProviderImportOrchestrator::class)->resume($run->fresh()))->toThrow(LogicException::class);
});

it('records cancellation intent without deleting audit history', function (): void {
    $provider = Provider::query()->create(['slug' => 'fixture', 'name' => 'Fixture', 'category' => 'metadata', 'status' => ProviderStatus::Approved, 'is_enabled' => true]);
    $run = ProviderImportRun::query()->create([
        'provider_id' => $provider->getKey(), 'operation' => 'artist-import', 'status' => ProviderImportRunStatus::Running,
        'configuration_hash' => str_repeat('b', 64), 'configuration' => ['entity_type' => 'artist', 'page_size' => 50, 'options' => []],
    ]);

    app(ProviderImportOrchestrator::class)->requestCancellation($run);

    expect($run->fresh()->cancellation_requested_at)->not->toBeNull();
});

it('records terminal provider request failures without creating payloads', function (): void {
    Queue::fake();
    $adapter = new class implements ProviderCatalogAdapter
    {
        public function providerSlug(): string
        {
            return 'fixture-failing';
        }

        public function capabilities(): array
        {
            return [ProviderCatalogCapability::ArtistLookup];
        }

        public function fetchPage(ProviderImportContext $context, ?string $cursor = null): ProviderPage
        {
            throw new ProviderRequestException(
                message: 'Fixture artist was not found.',
                kind: 'not-found',
                retryable: false,
                httpStatus: 404,
            );
        }

        public function normalize(ProviderPayload $payload): NormalizedProviderEntity
        {
            throw new LogicException('Normalization must not run for a failed request.');
        }
    };
    app()->instance(ProviderCatalogAdapterRegistry::class, new InMemoryProviderCatalogAdapterRegistry([$adapter]));
    $provider = Provider::query()->create(['slug' => 'fixture-failing', 'name' => 'Fixture failing', 'category' => 'metadata', 'status' => ProviderStatus::Approved, 'is_enabled' => true]);
    $run = app(ProviderImportOrchestrator::class)->start($provider, EntityType::Artist, 'artist-import', 'missing-artist');

    (new FetchProviderImportPage((string) $run->getKey()))->handle(
        app(ProviderCatalogAdapterRegistry::class),
        app(ProviderImportOrchestrator::class),
        app(ProviderRuntimeConfiguration::class),
    );

    $run->refresh();
    expect($run->status)->toBe(ProviderImportRunStatus::Failed)
        ->and($run->payloads()->count())->toBe(0)
        ->and($run->failures()->where('kind', 'not-found')->where('retryable', false)->exists())->toBeTrue();
});

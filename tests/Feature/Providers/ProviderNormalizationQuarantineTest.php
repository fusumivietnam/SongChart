<?php

declare(strict_types=1);

use App\Actions\Providers\Ingestion\RetryQuarantinedProviderImportItem;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Jobs\Providers\Ingestion\ProcessProviderImportPayload;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportItem;
use App\Models\Providers\Ingestion\ProviderImportPayload;
use App\Models\Providers\Ingestion\ProviderImportRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function createQuarantineTestItem(ProviderImportItemStatus $status): ProviderImportItem
{
    $provider = Provider::query()->create([
        'slug' => 'quarantine-fixture',
        'name' => 'Quarantine Fixture',
        'category' => 'metadata',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

    $run = ProviderImportRun::query()->create([
        'provider_id' => $provider->getKey(),
        'operation' => 'artist-import',
        'status' => ProviderImportRunStatus::Running,
        'configuration_hash' => hash('sha256', '{}'),
        'configuration' => ['entity_type' => 'artist'],
        'started_at' => now(),
    ]);

    $payload = ProviderImportPayload::query()->create([
        'provider_import_run_id' => $run->getKey(),
        'provider_entity_type' => 'artist',
        'provider_entity_id' => 'external-id',
        'payload_hash' => hash('sha256', '{"id":"external-id"}'),
        'payload' => ['id' => 'external-id'],
        'schema_version' => '1',
        'received_at' => now(),
    ]);

    return ProviderImportItem::query()->create([
        'provider_import_run_id' => $run->getKey(),
        'provider_import_payload_id' => $payload->getKey(),
        'provider_entity_type' => 'artist',
        'provider_entity_id' => 'external-id',
        'status' => $status,
        'attempts' => 1,
        'result' => ['validation_issues' => [['kind' => 'invalid-value']]],
        'processed_at' => now(),
    ]);
}

it('requeues a quarantined import item and dispatches its processing job', function (): void {
    Bus::fake();
    $item = createQuarantineTestItem(ProviderImportItemStatus::Quarantined);

    (new RetryQuarantinedProviderImportItem)->handle($item);

    $item->refresh();

    expect($item->status)->toBe(ProviderImportItemStatus::Pending)
        ->and($item->processed_at)->toBeNull()
        ->and($item->result)->toBeNull();

    Bus::assertDispatched(
        ProcessProviderImportPayload::class,
        fn (ProcessProviderImportPayload $job): bool => $job->itemId === (string) $item->getKey(),
    );
});

it('rejects retrying an import item that is not quarantined', function (): void {
    Bus::fake();
    $item = createQuarantineTestItem(ProviderImportItemStatus::Normalized);

    expect(fn () => (new RetryQuarantinedProviderImportItem)->handle($item))
        ->toThrow(DomainException::class, 'Only quarantined provider import items may be retried.');

    expect($item->refresh()->status)->toBe(ProviderImportItemStatus::Normalized);
    Bus::assertNotDispatched(ProcessProviderImportPayload::class);
});

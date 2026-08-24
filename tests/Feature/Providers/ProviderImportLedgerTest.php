<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportCheckpoint;
use App\Models\Providers\Ingestion\ProviderImportItem;
use App\Models\Providers\Ingestion\ProviderImportPayload;
use App\Models\Providers\Ingestion\ProviderImportRequest;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Support\Providers\Ingestion\SensitiveDataRedactor;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createImportRun(): ProviderImportRun
{
    $provider = Provider::query()->create([
        'slug' => 'fixture-provider',
        'name' => 'Fixture Provider',
        'category' => 'metadata',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

    return ProviderImportRun::query()->create([
        'provider_id' => $provider->id,
        'operation' => 'catalog-import',
        'status' => ProviderImportRunStatus::Running,
        'configuration_hash' => hash('sha256', '{}'),
        'configuration' => ['entity_type' => 'artist'],
        'started_at' => now(),
    ]);
}

it('persists an auditable import run request payload item and checkpoint', function (): void {
    $run = createImportRun();
    $request = ProviderImportRequest::query()->create([
        'provider_import_run_id' => $run->id,
        'sequence' => 1,
        'method' => 'GET',
        'endpoint' => 'https://example.test/artists/abc',
        'query' => ['inc' => 'aliases'],
        'request_headers' => ['Accept' => 'application/json'],
        'response_status' => 200,
        'duration_ms' => 42,
        'requested_at' => now(),
        'responded_at' => now(),
    ]);
    $payloadData = ['id' => 'abc', 'name' => 'Example Artist'];
    $payload = ProviderImportPayload::query()->create([
        'provider_import_run_id' => $run->id,
        'provider_import_request_id' => $request->id,
        'provider_entity_type' => 'artist',
        'provider_entity_id' => 'abc',
        'payload_hash' => hash('sha256', json_encode($payloadData, JSON_THROW_ON_ERROR)),
        'payload' => $payloadData,
        'schema_version' => '1',
        'received_at' => now(),
    ]);
    ProviderImportItem::query()->create([
        'provider_import_run_id' => $run->id,
        'provider_import_payload_id' => $payload->id,
        'provider_entity_type' => 'artist',
        'provider_entity_id' => 'abc',
        'status' => ProviderImportItemStatus::Pending,
    ]);
    ProviderImportCheckpoint::query()->create([
        'provider_import_run_id' => $run->id,
        'checkpoint_key' => 'catalog-page',
        'cursor' => 'next-1',
        'state' => ['processed' => 1],
        'committed_at' => now(),
    ]);

    expect($run->fresh()->requests)->toHaveCount(1)
        ->and($run->fresh()->payloads)->toHaveCount(1)
        ->and($run->fresh()->items)->toHaveCount(1)
        ->and($run->fresh()->checkpoints)->toHaveCount(1);
});

it('deduplicates payloads and checkpoints across sqlite and postgres', function (): void {
    $run = createImportRun();
    $attributes = [
        'provider_import_run_id' => $run->id,
        'provider_entity_type' => 'artist',
        'provider_entity_id' => 'abc',
        'payload_hash' => str_repeat('a', 64),
        'payload' => ['id' => 'abc'],
        'received_at' => now(),
    ];
    ProviderImportPayload::query()->create($attributes);

    expect(fn () => DB::transaction(fn () => ProviderImportPayload::query()->create($attributes)))
        ->toThrow(QueryException::class);

    ProviderImportCheckpoint::query()->create([
        'provider_import_run_id' => $run->id,
        'checkpoint_key' => 'page',
        'cursor' => '1',
        'committed_at' => now(),
    ]);

    expect(fn () => DB::transaction(fn () => ProviderImportCheckpoint::query()->create([
        'provider_import_run_id' => $run->id,
        'checkpoint_key' => 'page',
        'cursor' => '2',
        'committed_at' => now(),
    ])))->toThrow(QueryException::class);
});

it('keeps raw provider payloads immutable', function (): void {
    $run = createImportRun();
    $payload = ProviderImportPayload::query()->create([
        'provider_import_run_id' => $run->id,
        'provider_entity_type' => 'artist',
        'provider_entity_id' => 'abc',
        'payload_hash' => str_repeat('b', 64),
        'payload' => ['id' => 'abc'],
        'received_at' => now(),
    ]);

    $payload->payload = ['id' => 'changed'];
    expect(fn () => $payload->save())->toThrow(LogicException::class, 'immutable');
});

it('redacts secrets recursively before audit persistence', function (): void {
    $redacted = (new SensitiveDataRedactor)->redact([
        'Authorization' => 'Bearer secret',
        'nested' => ['access_token' => 'secret', 'safe' => 'value'],
        'Accept' => 'application/json',
    ]);

    expect($redacted['Authorization'])->toBe('[REDACTED]')
        ->and($redacted['nested']['access_token'])->toBe('[REDACTED]')
        ->and($redacted['nested']['safe'])->toBe('value')
        ->and($redacted['Accept'])->toBe('application/json');
});

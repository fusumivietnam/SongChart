<?php

declare(strict_types=1);

namespace App\Jobs\Providers\Ingestion;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Ingestion\Enums\ProviderImportFailureStage;
use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Models\Providers\Ingestion\ProviderImportFailure;
use App\Models\Providers\Ingestion\ProviderImportItem;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

final class ProcessProviderImportPayload implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public readonly string $itemId)
    {
        $this->onQueue((string) config('songchart.providers.ingestion.normalization_queue', 'provider-normalization'));
    }

    /** @return list<string> */
    public function tags(): array
    {
        return ['provider-normalization', 'item:'.$this->itemId];
    }

    public function uniqueId(): string
    {
        return $this->itemId;
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(
        ProviderCatalogAdapterRegistry $registry,
        NormalizedProviderEntityValidator $validator,
        CanonicalMutationPipeline $mutationPipeline,
    ): void {
        $item = ProviderImportItem::query()->with(['run.provider', 'payload'])->findOrFail($this->itemId);
        if ($item->run->getAttribute('cancellation_requested_at') !== null) {
            $item->forceFill([
                'status' => ProviderImportItemStatus::Skipped,
                'processed_at' => now(),
                'result' => ['reason' => 'cancelled'],
            ])->save();

            return;
        }

        $adapter = $registry->for((string) $item->run->provider->slug);
        if ($adapter === null) {
            throw new RuntimeException('Provider catalog adapter is unavailable.');
        }

        /** @var array<string, mixed> $data */
        $data = $item->payload->getAttribute('payload');
        $payload = new ProviderPayload(
            providerSlug: (string) $item->run->provider->slug,
            entityType: EntityType::from((string) $item->provider_entity_type),
            externalId: (string) $item->provider_entity_id,
            data: $data,
            receivedAt: new DateTimeImmutable((string) $item->payload->received_at),
            schemaVersion: (string) $item->payload->schema_version,
        );

        $normalized = $adapter->normalize($payload);
        $validation = $validator->validate($normalized);

        if (! $validation->isValid()) {
            $item->forceFill([
                'status' => ProviderImportItemStatus::Quarantined,
                'normalizer_version' => $normalized->normalizerVersion,
                'attempts' => ((int) $item->attempts) + 1,
                'result' => [
                    'normalized' => $normalized->toArray(),
                    'validation_issues' => $validation->toArray(),
                ],
                'processed_at' => now(),
            ])->save();

            foreach ($validation->issues as $issue) {
                ProviderImportFailure::query()->create([
                    'provider_import_run_id' => $item->provider_import_run_id,
                    'provider_import_item_id' => $item->getKey(),
                    'stage' => ProviderImportFailureStage::Validation,
                    'kind' => $issue->kind->value,
                    'message' => $issue->message,
                    'context' => ['path' => $issue->path, ...$issue->context],
                    'retryable' => false,
                    'occurred_at' => now(),
                ]);
            }

            return;
        }

        $mutation = $mutationPipeline->apply($normalized);

        $item->forceFill([
            'status' => ProviderImportItemStatus::Applied,
            'canonical_entity_type' => $mutation->entityType,
            'canonical_entity_id' => $mutation->entityId,
            'normalizer_version' => $normalized->normalizerVersion,
            'attempts' => ((int) $item->attempts) + 1,
            'result' => [
                'normalized' => $normalized->toArray(),
                'mutation' => $mutation->toArray(),
            ],
            'processed_at' => now(),
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        $item = ProviderImportItem::query()->find($this->itemId);
        if ($item === null) {
            return;
        }
        $item->forceFill([
            'status' => ProviderImportItemStatus::Failed,
            'processed_at' => now(),
        ])->save();

        ProviderImportFailure::query()->create([
            'provider_import_run_id' => $item->provider_import_run_id,
            'provider_import_item_id' => $item->getKey(),
            'stage' => ProviderImportFailureStage::Normalization,
            'kind' => 'job-exhausted',
            'message' => $exception?->getMessage() ?? 'Provider payload normalization exhausted its retries.',
            'retryable' => false,
            'occurred_at' => now(),
        ]);
    }
}

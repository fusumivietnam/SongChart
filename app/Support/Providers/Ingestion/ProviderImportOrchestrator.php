<?php

declare(strict_types=1);

namespace App\Support\Providers\Ingestion;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Jobs\Providers\Ingestion\FetchProviderImportPage;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportCheckpoint;
use App\Models\Providers\Ingestion\ProviderImportRun;
use Illuminate\Support\Facades\DB;
use LogicException;

final class ProviderImportOrchestrator
{
    /** @param array<string, scalar|null> $options */
    public function start(Provider $provider, EntityType $entityType, string $operation, ?string $externalId = null, ?string $query = null, int $pageSize = 50, array $options = []): ProviderImportRun
    {
        $run = DB::transaction(function () use ($provider, $entityType, $operation, $externalId, $query, $pageSize, $options): ProviderImportRun {
            $configuration = [
                'entity_type' => $entityType->value,
                'external_id' => $externalId,
                'query' => $query,
                'page_size' => $pageSize,
                'options' => $options,
            ];

            $run = ProviderImportRun::query()->create([
                'provider_id' => $provider->getKey(),
                'operation' => $operation,
                'status' => ProviderImportRunStatus::Queued,
                'configuration_hash' => hash('sha256', json_encode($configuration, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)),
                'configuration' => $configuration,
                'statistics' => ['pages' => 0, 'payloads' => 0, 'normalized' => 0, 'failed' => 0],
            ]);

            return $run;
        });

        FetchProviderImportPage::dispatch((string) $run->getKey());

        return $run;
    }

    public function resume(ProviderImportRun $run): void
    {
        $status = $this->status($run);
        if ($status->isTerminal()) {
            throw new LogicException('Terminal provider import runs cannot be resumed.');
        }

        $this->transition($run, ProviderImportRunStatus::Queued, ['resume_after' => null]);
        FetchProviderImportPage::dispatch((string) $run->getKey(), $this->cursor($run));
    }

    public function requestCancellation(ProviderImportRun $run): void
    {
        if ($this->status($run)->isTerminal()) {
            return;
        }

        $run->forceFill(['cancellation_requested_at' => now()])->save();
    }

    /** @param array<string, mixed> $attributes */
    public function transition(ProviderImportRun $run, ProviderImportRunStatus $next, array $attributes = []): void
    {
        $current = $this->status($run);
        if ($current !== $next) {
            $current->assertCanTransitionTo($next);
        }

        $run->forceFill(array_merge($attributes, ['status' => $next]))->save();
    }

    public function status(ProviderImportRun $run): ProviderImportRunStatus
    {
        $value = $run->getAttribute('status');

        return $value instanceof ProviderImportRunStatus ? $value : ProviderImportRunStatus::from((string) $value);
    }

    public function cursor(ProviderImportRun $run): ?string
    {
        $checkpoint = ProviderImportCheckpoint::query()
            ->where('provider_import_run_id', $run->getKey())
            ->where('checkpoint_key', 'page')
            ->first();

        return $checkpoint?->cursor;
    }

    public function context(ProviderImportRun $run): ProviderImportContext
    {
        /** @var array<string, mixed> $configuration */
        $configuration = $run->getAttribute('configuration') ?? [];

        return new ProviderImportContext(
            runId: (string) $run->getKey(),
            entityType: EntityType::from((string) $configuration['entity_type']),
            externalId: isset($configuration['external_id']) ? (string) $configuration['external_id'] : null,
            query: isset($configuration['query']) ? (string) $configuration['query'] : null,
            pageSize: (int) ($configuration['page_size'] ?? 50),
            options: is_array($configuration['options'] ?? null) ? $configuration['options'] : [],
        );
    }
}

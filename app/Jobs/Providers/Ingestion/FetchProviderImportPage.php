<?php

declare(strict_types=1);

namespace App\Jobs\Providers\Ingestion;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use App\Domain\Providers\Ingestion\Enums\ProviderImportFailureStage;
use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Providers\Ingestion\ProviderImportCheckpoint;
use App\Models\Providers\Ingestion\ProviderImportFailure;
use App\Models\Providers\Ingestion\ProviderImportItem;
use App\Models\Providers\Ingestion\ProviderImportPayload;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class FetchProviderImportPage implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    public function __construct(public readonly string $runId, public readonly ?string $cursor = null)
    {
        $this->onQueue((string) config('songchart.providers.ingestion.import_queue', 'provider-imports'));
    }

    /** @return list<string> */
    public function tags(): array
    {
        return ['provider-import', 'run:'.$this->runId];
    }

    public function uniqueId(): string
    {
        return $this->runId.':'.($this->cursor ?? 'start');
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 30, 120, 300];
    }

    /** @return list<object> */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('provider-import-'.$this->runId))->expireAfter(180)];
    }

    public function handle(
        ProviderCatalogAdapterRegistry $registry,
        ProviderImportOrchestrator $orchestrator,
        ProviderRuntimeConfiguration $runtimeConfiguration,
    ): void {
        $run = ProviderImportRun::query()->with('provider')->findOrFail($this->runId);
        if ($run->getAttribute('cancellation_requested_at') !== null) {
            $orchestrator->transition($run, ProviderImportRunStatus::Cancelled, ['finished_at' => now()]);

            return;
        }

        $status = $orchestrator->status($run);
        if ($status->isTerminal()) {
            return;
        }
        if ($status !== ProviderImportRunStatus::Running) {
            $orchestrator->transition($run, ProviderImportRunStatus::Running, [
                'started_at' => $run->getAttribute('started_at') ?? now(),
                'heartbeat_at' => now(),
                'attempts' => ((int) $run->getAttribute('attempts')) + 1,
            ]);
        }

        $provider = $run->provider;
        $runtimeConfiguration->apply((string) $provider->slug);
        $adapter = $registry->for((string) $provider->slug);
        if ($adapter === null) {
            throw new RuntimeException("No provider catalog adapter is registered for [{$provider->slug}].");
        }

        try {
            $page = $adapter->fetchPage($orchestrator->context($run), $this->cursor);
        } catch (ProviderRequestException $exception) {
            $this->recordRequestFailure($run, $exception);

            if (! $exception->retryable) {
                $run->forceFill([
                    'status' => ProviderImportRunStatus::Failed,
                    'error_summary' => $exception->getMessage(),
                    'finished_at' => now(),
                    'resume_after' => null,
                ])->save();

                return;
            }

            $delay = $exception->retryAfterSeconds ?? $this->retryDelayForAttempt();
            $orchestrator->transition($run, ProviderImportRunStatus::Retrying, [
                'error_summary' => $exception->getMessage(),
                'resume_after' => now()->addSeconds($delay),
                'heartbeat_at' => now(),
            ]);

            if ($this->attempts() >= $this->tries) {
                throw $exception;
            }

            $this->release($delay);

            return;
        }

        $itemIds = DB::transaction(function () use ($run, $page): array {
            $itemIds = [];
            foreach ($page->items as $payload) {
                $stored = ProviderImportPayload::query()->firstOrCreate([
                    'provider_import_run_id' => $run->getKey(),
                    'provider_entity_type' => $payload->entityType->value,
                    'provider_entity_id' => $payload->externalId,
                    'payload_hash' => $payload->hash(),
                ], [
                    'payload' => $payload->data,
                    'schema_version' => $payload->schemaVersion,
                    'received_at' => $payload->receivedAt,
                ]);

                $item = ProviderImportItem::query()->firstOrCreate([
                    'provider_import_run_id' => $run->getKey(),
                    'provider_entity_type' => $payload->entityType->value,
                    'provider_entity_id' => $payload->externalId,
                ], [
                    'provider_import_payload_id' => $stored->getKey(),
                    'status' => ProviderImportItemStatus::Pending,
                ]);

                $itemIds[] = (string) $item->getKey();
            }

            ProviderImportCheckpoint::query()->updateOrCreate([
                'provider_import_run_id' => $run->getKey(),
                'checkpoint_key' => 'page',
            ], [
                'cursor' => $page->nextCursor,
                'state' => ['complete' => $page->complete, 'item_count' => count($page->items)],
                'committed_at' => now(),
            ]);

            /** @var array<string, int> $statistics */
            $statistics = $run->getAttribute('statistics') ?? [];
            $statistics['pages'] = ($statistics['pages'] ?? 0) + 1;
            $statistics['payloads'] = ($statistics['payloads'] ?? 0) + count($page->items);
            $run->forceFill(['cursor_end' => $page->nextCursor, 'statistics' => $statistics, 'heartbeat_at' => now()])->save();

            return $itemIds;
        });

        foreach ($itemIds as $itemId) {
            ProcessProviderImportPayload::dispatch($itemId);
        }

        if ($page->complete || $page->nextCursor === null) {
            FinalizeProviderImport::dispatch($this->runId);

            return;
        }

        self::dispatch($this->runId, $page->nextCursor);
    }

    private function retryDelayForAttempt(): int
    {
        $delays = $this->backoff();
        $index = max(0, min(count($delays) - 1, $this->attempts() - 1));

        return $delays[$index];
    }

    private function recordRequestFailure(ProviderImportRun $run, ProviderRequestException $exception): void
    {
        ProviderImportFailure::query()->create([
            'provider_import_run_id' => $run->getKey(),
            'stage' => ProviderImportFailureStage::Request,
            'kind' => $exception->kind,
            'error_code' => $exception->httpStatus === null ? null : (string) $exception->httpStatus,
            'message' => $exception->getMessage(),
            'context' => [
                'attempt' => $this->attempts(),
                'cursor' => $this->cursor,
                'retry_after_seconds' => $exception->retryAfterSeconds,
            ],
            'retryable' => $exception->retryable,
            'occurred_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $run = ProviderImportRun::query()->find($this->runId);
        if ($run === null) {
            return;
        }

        ProviderImportFailure::query()->create([
            'provider_import_run_id' => $run->getKey(),
            'stage' => ProviderImportFailureStage::Request,
            'kind' => 'job-exhausted',
            'message' => $exception?->getMessage() ?? 'Provider import page job exhausted its retries.',
            'retryable' => false,
            'occurred_at' => now(),
        ]);

        $run->forceFill(['status' => ProviderImportRunStatus::Failed, 'error_summary' => $exception?->getMessage(), 'finished_at' => now()])->save();
    }
}

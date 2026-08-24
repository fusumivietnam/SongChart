<?php

declare(strict_types=1);

namespace App\Jobs\Providers\Ingestion;

use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Providers\Ingestion\ProviderImportRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class FinalizeProviderImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 20;

    public function __construct(public readonly string $runId)
    {
        $this->onQueue((string) config('songchart.providers.ingestion.import_queue', 'provider-imports'));
    }

    /** @return list<string> */
    public function tags(): array
    {
        return ['provider-import-finalize', 'run:'.$this->runId];
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 10, 20, 30];
    }

    public function handle(): void
    {
        $run = ProviderImportRun::query()->findOrFail($this->runId);
        if ($run->getAttribute('cancellation_requested_at') !== null) {
            $run->forceFill(['status' => ProviderImportRunStatus::Cancelled, 'finished_at' => now()])->save();

            return;
        }

        $pending = $run->items()->whereIn('status', [ProviderImportItemStatus::Pending->value])->exists();
        if ($pending) {
            $this->release(5);

            return;
        }

        $failed = $run->items()->where('status', ProviderImportItemStatus::Failed->value)->count();
        $normalized = $run->items()->whereIn('status', [
            ProviderImportItemStatus::Normalized->value,
            ProviderImportItemStatus::Matched->value,
            ProviderImportItemStatus::Applied->value,
        ])->count();
        $status = $failed > 0 ? ProviderImportRunStatus::CompletedWithErrors : ProviderImportRunStatus::Completed;

        /** @var array<string, int> $statistics */
        $statistics = $run->getAttribute('statistics') ?? [];
        $statistics['normalized'] = $normalized;
        $statistics['failed'] = $failed;

        $run->forceFill(['status' => $status, 'statistics' => $statistics, 'finished_at' => now(), 'heartbeat_at' => now()])->save();
    }
}

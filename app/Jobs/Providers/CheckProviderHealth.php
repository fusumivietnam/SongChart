<?php

declare(strict_types=1);

namespace App\Jobs\Providers;

use App\Domain\Providers\Enums\ProviderSyncOperation;
use App\Domain\Providers\Enums\ProviderSyncStatus;
use App\Models\Provider;
use App\Models\ProviderSyncRun;
use App\Support\Providers\ProviderAdapterRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class CheckProviderHealth implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public int $uniqueFor = 900;

    public function __construct(public readonly string $providerId, public readonly string $syncRunId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function uniqueId(): string
    {
        return $this->providerId.':'.ProviderSyncOperation::HealthCheck->value;
    }

    /** @return list<string> */
    public function tags(): array
    {
        return ['provider-health', 'provider:'.$this->providerId, 'sync-run:'.$this->syncRunId];
    }

    public function handle(ProviderAdapterRegistry $registry): void
    {
        $run = ProviderSyncRun::query()->find($this->syncRunId);
        if (! $run instanceof ProviderSyncRun) {
            return;
        }

        $statusAttribute = $run->getAttribute('status');
        $status = $statusAttribute instanceof ProviderSyncStatus
            ? $statusAttribute
            : ProviderSyncStatus::tryFrom((string) $statusAttribute);

        if ($status?->isTerminal() === true) {
            return;
        }

        $provider = Provider::query()->find($this->providerId);
        if (! $provider instanceof Provider) {
            $this->finish($run, ProviderSyncStatus::Failed, 'Provider record no longer exists.', 0, 1);

            return;
        }
        if (! $provider->is_enabled) {
            $this->finish($run, ProviderSyncStatus::Skipped, 'Provider is disabled.', 0, 0);

            return;
        }

        $adapter = $registry->for($provider->slug);
        if ($adapter === null) {
            $this->finish($run, ProviderSyncStatus::Skipped, 'No provider adapter is registered.', 0, 0);

            return;
        }

        $run->forceFill(['status' => ProviderSyncStatus::Running, 'started_at' => $run->started_at ?? now(), 'finished_at' => null, 'error_summary' => null])->save();

        try {
            $health = $adapter->healthCheck();
            $this->finish($run, $health->healthy ? ProviderSyncStatus::Succeeded : ProviderSyncStatus::Failed, $health->summary, 1, $health->healthy ? 0 : 1);
        } catch (Throwable $exception) {
            $run->forceFill(['status' => ProviderSyncStatus::Retrying, 'finished_at' => null, 'failed_count' => 1, 'error_summary' => $this->summary($exception->getMessage())])->save();
            report($exception);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $run = ProviderSyncRun::query()->find($this->syncRunId);
        if (! $run instanceof ProviderSyncRun) {
            return;
        }
        $this->finish($run, ProviderSyncStatus::Failed, $exception === null ? 'Provider health check exhausted its retries.' : $exception->getMessage(), 0, 1);
    }

    private function finish(ProviderSyncRun $run, ProviderSyncStatus $status, string $summary, int $processed, int $failed): void
    {
        $run->forceFill(['status' => $status, 'finished_at' => now(), 'processed_count' => $processed, 'failed_count' => $failed, 'error_summary' => $this->summary($summary)])->save();
    }

    private function summary(string $summary): string
    {
        return mb_substr(trim($summary), 0, 1000);
    }
}

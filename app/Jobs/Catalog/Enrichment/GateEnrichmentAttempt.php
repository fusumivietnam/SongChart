<?php

declare(strict_types=1);

namespace App\Jobs\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GateEnrichmentAttempt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public readonly string $attemptId)
    {
        $this->onQueue((string) config('songchart.providers.ingestion.import_queue', 'provider-imports'));
    }

    public function handle(
        EnrichmentAttemptStore $attempts,
        ProviderRatePolicyRegistry $policies,
        ProviderRequestGate $gate,
    ): void {
        $attempt = $attempts->beginGate($this->attemptId);
        if ($attempt === null) {
            return;
        }

        $operation = 'enrichment.'.$attempt['need_kind'].'.'.$attempt['need_key'];
        $policy = $policies->for($attempt['provider'], $operation);

        try {
            $gate->await($policy);
            $attempts->markReady($this->attemptId);
            ExecuteEnrichmentAttempt::dispatch($this->attemptId);
        } catch (ProviderRequestException $exception) {
            if ($exception->retryable) {
                $attempts->markDeferred($this->attemptId, $exception->getMessage());
                $this->release(max(1, $exception->retryAfterSeconds ?? 1));

                return;
            }

            $attempts->markFailed($this->attemptId, $exception->getMessage());
            throw $exception;
        }
    }
}

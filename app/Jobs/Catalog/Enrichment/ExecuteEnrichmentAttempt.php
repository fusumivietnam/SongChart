<?php

declare(strict_types=1);

namespace App\Jobs\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Contracts\Catalog\EnrichmentEvidenceAdmissionPolicy;
use App\Contracts\Catalog\EnrichmentExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ExecuteEnrichmentAttempt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly string $attemptId)
    {
        $this->onQueue((string) config('songchart.providers.ingestion.import_queue', 'provider-imports'));
    }

    public function handle(
        EnrichmentAttemptStore $attempts,
        EnrichmentExecutor $executor,
        EnrichmentEvidenceAdmissionPolicy $admission,
    ): void {
        $attempt = $attempts->beginExecution($this->attemptId);
        if ($attempt === null) {
            return;
        }

        try {
            $result = $executor->execute($attempt);
        } catch (Throwable $exception) {
            $attempts->markFailed($this->attemptId, $exception->getMessage());
            throw $exception;
        }

        if ($result->outcome === 'succeeded') {
            $decision = $admission->assess($attempt, $result->payload);

            match ($decision->decision) {
                'admissible' => $attempts->markSucceeded($this->attemptId, $decision->payload),
                'review_required' => $attempts->markReviewRequired($this->attemptId, $decision->reason, $decision->payload),
                'rejected' => $attempts->markRejected($this->attemptId, $decision->reason, $decision->payload),
                default => $attempts->markFailed($this->attemptId, 'Unknown enrichment evidence admission decision.'),
            };

            return;
        }

        match ($result->outcome) {
            'retryable' => $this->retry($attempts, $result->message ?? 'Retry requested.', $result->retryAfterSeconds ?? 1),
            'review_required' => $attempts->markReviewRequired($this->attemptId, $result->message ?? 'Manual review required.', $result->payload),
            'failed' => $attempts->markFailed($this->attemptId, $result->message ?? 'Provider enrichment failed.'),
            default => $attempts->markFailed($this->attemptId, 'Unknown enrichment execution outcome.'),
        };
    }

    private function retry(EnrichmentAttemptStore $attempts, string $message, int $delay): void
    {
        $attempts->markDeferred($this->attemptId, $message);
        GateEnrichmentAttempt::dispatch($this->attemptId)->delay(now()->addSeconds(max(1, $delay)));
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Jobs\Catalog\Enrichment\GateEnrichmentAttempt;

final readonly class DispatchEnrichmentAttempts
{
    public function __construct(private EnrichmentAttemptStore $attempts) {}

    /** @param list<string> $attemptIds */
    public function handle(array $attemptIds): void
    {
        foreach (array_values(array_unique($attemptIds)) as $attemptId) {
            $this->attempts->markQueued($attemptId);
            GateEnrichmentAttempt::dispatch($attemptId);
        }
    }
}

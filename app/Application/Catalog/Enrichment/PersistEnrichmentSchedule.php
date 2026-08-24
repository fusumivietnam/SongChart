<?php

declare(strict_types=1);

namespace App\Application\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Domain\Catalog\Enrichment\EnrichmentSchedule;

final readonly class PersistEnrichmentSchedule
{
    public function __construct(private EnrichmentAttemptStore $attempts) {}

    /** @return list<string> */
    public function handle(EnrichmentSchedule $schedule): array
    {
        $attemptIds = [];

        foreach ($schedule->dispatches as $dispatch) {
            $attemptIds[] = $this->attempts->reserve($dispatch);
        }

        return $attemptIds;
    }
}

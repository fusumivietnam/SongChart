<?php

declare(strict_types=1);

namespace App\Application\Chart\Listeners;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Events\CanonicalEntityChanged;
use App\Jobs\Chart\RefreshYouTubeViewChartJob;

final readonly class RefreshChartAfterCanonicalChange
{
    public function handle(CanonicalEntityChanged $event): void
    {
        if ($event->entityType !== EntityType::Recording) {
            return;
        }

        if (! (bool) config('songchart.providers.youtube.enabled')) {
            return;
        }

        RefreshYouTubeViewChartJob::dispatch();
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Chart\Listeners;

use App\Application\Chart\PlanChartRecompute;
use App\Application\Chart\RefreshYouTubeViewChart;
use App\Domain\Catalog\Events\CanonicalEntityChanged;
use App\Jobs\Chart\RefreshYouTubeViewChartJob;

final readonly class RefreshChartAfterCanonicalChange
{
    public function __construct(private PlanChartRecompute $planner) {}

    public function handle(CanonicalEntityChanged $event): void
    {
        foreach ($this->planner->forCanonicalChange($event->entityType) as $chartId) {
            if ($chartId === RefreshYouTubeViewChart::CHART_ID
                && (bool) config('songchart.providers.youtube.enabled')) {
                RefreshYouTubeViewChartJob::dispatch();
            }
        }
    }
}

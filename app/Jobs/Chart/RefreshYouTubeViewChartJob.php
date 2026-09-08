<?php

declare(strict_types=1);

namespace App\Jobs\Chart;

use App\Application\Chart\RefreshYouTubeViewChart;
use App\Enums\QueueName;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RefreshYouTubeViewChartJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 900;

    public function __construct()
    {
        $this->onQueue(QueueName::Default->value);
    }

    public function uniqueId(): string
    {
        return RefreshYouTubeViewChart::CHART_ID;
    }

    public function handle(RefreshYouTubeViewChart $refresh): void
    {
        $refresh->handle();
    }
}

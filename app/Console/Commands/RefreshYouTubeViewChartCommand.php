<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Chart\RefreshYouTubeViewChart;
use Illuminate\Console\Command;

final class RefreshYouTubeViewChartCommand extends Command
{
    protected $signature = 'charts:refresh-youtube-views';

    protected $description = 'Fetch approved YouTube destination view counts and append a provenance-bearing chart snapshot.';

    public function handle(RefreshYouTubeViewChart $refresh): int
    {
        $snapshotId = $refresh->handle();
        if ($snapshotId === null) {
            $this->info('No approved public YouTube destinations were available; no chart snapshot was created.');

            return self::SUCCESS;
        }

        $this->info('YouTube view chart snapshot appended: '.$snapshotId);

        return self::SUCCESS;
    }
}

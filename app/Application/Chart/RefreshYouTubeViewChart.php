<?php

declare(strict_types=1);

namespace App\Application\Chart;

use App\Support\Chart\DatabaseChartSnapshotStore;
use App\Support\Chart\YouTubeViewCountObservationSource;
use DateTimeImmutable;
use DateTimeZone;

final readonly class RefreshYouTubeViewChart
{
    public const CHART_ID = 'youtube-video-views';

    public function __construct(
        private YouTubeViewCountObservationSource $source,
        private DatabaseChartSnapshotStore $store,
    ) {}

    public function handle(): ?string
    {
        $observations = $this->source->fetchApproved();
        if ($observations === []) {
            return null;
        }

        $snapshotAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $snapshot = (new BuildChartSnapshot)->handle(
            self::CHART_ID,
            YouTubeViewCountObservationSource::METRIC,
            $snapshotAt,
            $observations,
        );

        return $this->store->append($snapshot);
    }
}

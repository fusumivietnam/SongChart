<?php

declare(strict_types=1);

namespace App\Application\Chart;

use App\Support\Chart\DatabaseChartMetricObservationStore;
use App\Support\Chart\DatabaseChartSnapshotStore;
use App\Support\Chart\YouTubeViewCountObservationSource;
use DateTimeImmutable;
use DateTimeZone;

final readonly class RefreshYouTubeViewChart
{
    public const CHART_ID = 'youtube-video-views';

    public function __construct(
        private YouTubeViewCountObservationSource $source,
        private DatabaseChartMetricObservationStore $observations,
        private DatabaseChartSnapshotStore $store,
    ) {}

    public function handle(): ?string
    {
        $fetched = $this->source->fetchApproved();
        if ($fetched === []) {
            return null;
        }

        $observations = $this->observations->appendMany($fetched);
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

<?php

declare(strict_types=1);

namespace App\Domain\Chart\DTO;

use DateTimeImmutable;

final readonly class ChartMetricObservation
{
    public function __construct(
        public string $observationId,
        public string $canonicalRecordingId,
        public string $provider,
        public string $providerItemId,
        public string $metric,
        public int|float $value,
        public DateTimeImmutable $observedAt,
    ) {}
}

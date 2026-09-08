<?php

declare(strict_types=1);

namespace App\Domain\Chart\DTO;

use DateTimeImmutable;

final readonly class ChartSnapshot
{
    /** @param list<array{rank:int,canonical_recording_id:string,score:float,observation_ids:list<string>}> $rows */
    public function __construct(
        public string $chartId,
        public string $metric,
        public string $calculationVersion,
        public DateTimeImmutable $snapshotAt,
        public array $rows,
    ) {}
}

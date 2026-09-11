<?php

declare(strict_types=1);

namespace App\Domain\Chart\DTO;

use DateTimeImmutable;

final readonly class ChartSnapshot
{
    /**
     * @param list<array{
     *   rank:int,
     *   canonical_recording_id:string,
     *   score:float,
     *   observation_ids:list<string>,
     *   observations:list<array{
     *     observation_id:string,
     *     provider:string,
     *     provider_item_id:string,
     *     metric:string,
     *     value:float,
     *     observed_at:string,
     *     metric_unit:string,
     *     metric_semantics_version:string,
     *     fetched_at:string|null,
     *     source_reference:string|null
     *   }>
     * }> $rows
     */
    public function __construct(
        public string $chartId,
        public string $metric,
        public string $calculationVersion,
        public string $inputFingerprint,
        public DateTimeImmutable $snapshotAt,
        public array $rows,
    ) {}
}

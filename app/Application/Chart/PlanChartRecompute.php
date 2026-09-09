<?php

declare(strict_types=1);

namespace App\Application\Chart;

use App\Domain\Catalog\Enums\EntityType;
use App\Support\Chart\ChartDefinitionRegistry;

final readonly class PlanChartRecompute
{
    public function __construct(private ChartDefinitionRegistry $definitions) {}

    /** @return list<string> */
    public function forCanonicalChange(EntityType $entityType): array
    {
        return array_map(
            static fn (array $definition): string => (string) $definition['id'],
            $this->definitions->activeForEntityType($entityType),
        );
    }
}

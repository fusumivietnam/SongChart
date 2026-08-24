<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class EnrichmentPlan
{
    /** @param list<EnrichmentNeed> $needs */
    public function __construct(
        public array $needs,
        public int $completeness,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'completeness' => $this->completeness,
            'needs' => array_map(static fn (EnrichmentNeed $need): array => $need->toArray(), $this->needs),
        ];
    }
}

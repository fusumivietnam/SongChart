<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class EnrichmentSchedule
{
    /**
     * @param  list<EnrichmentDispatch>  $dispatches
     * @param  list<EnrichmentNeed>  $deferred
     */
    public function __construct(
        public array $dispatches,
        public array $deferred,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'dispatches' => array_map(static fn (EnrichmentDispatch $dispatch): array => $dispatch->toArray(), $this->dispatches),
            'deferred' => array_map(static fn (EnrichmentNeed $need): array => $need->toArray(), $this->deferred),
        ];
    }
}

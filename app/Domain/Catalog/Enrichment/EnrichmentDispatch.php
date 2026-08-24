<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class EnrichmentDispatch
{
    public function __construct(
        public string $entityType,
        public string $entityId,
        public EnrichmentNeed $need,
        public string $idempotencyKey,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'idempotency_key' => $this->idempotencyKey,
            'need' => $this->need->toArray(),
        ];
    }
}

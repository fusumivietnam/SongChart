<?php

declare(strict_types=1);

namespace App\Domain\Providers\Ingestion\DTO;

use App\Domain\Catalog\Enums\EntityType;

final readonly class ProviderImportPlan
{
    /**
     * @param  array<string, int>  $counts
     * @param  list<string>  $reviewReasons
     */
    public function __construct(
        public string $providerSlug,
        public EntityType $entityType,
        public string $externalId,
        public string $operation,
        public bool $executable,
        public array $counts,
        public array $reviewReasons,
        public string $fingerprint,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'provider_slug' => $this->providerSlug,
            'entity_type' => $this->entityType->value,
            'external_id' => $this->externalId,
            'operation' => $this->operation,
            'executable' => $this->executable,
            'counts' => $this->counts,
            'review_reasons' => $this->reviewReasons,
            'fingerprint' => $this->fingerprint,
        ];
    }
}

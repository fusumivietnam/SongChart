<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class EnrichmentNeed
{
    public function __construct(
        public string $kind,
        public string $key,
        public string $provider,
        public string $priority,
        public string $costClass,
        public string $reason,
        public bool $providerEnabled,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'key' => $this->key,
            'provider' => $this->provider,
            'priority' => $this->priority,
            'cost_class' => $this->costClass,
            'reason' => $this->reason,
            'provider_enabled' => $this->providerEnabled,
        ];
    }
}

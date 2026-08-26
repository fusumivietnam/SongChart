<?php

declare(strict_types=1);

namespace App\Domain\Providers\Ingestion\DTO;

final readonly class ProviderImportPreview
{
    /**
     * @param  array<string, int>  $counts
     * @param  list<array{kind: string, path: string, message: string}>  $issues
     * @param  array<string, mixed>  $normalized
     */
    public function __construct(
        public bool $valid,
        public array $counts,
        public array $issues,
        public array $normalized,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'counts' => $this->counts,
            'issues' => $this->issues,
            'normalized' => $this->normalized,
        ];
    }
}

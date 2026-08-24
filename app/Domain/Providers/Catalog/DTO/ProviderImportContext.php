<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\DTO;

use App\Domain\Catalog\Enums\EntityType;

final readonly class ProviderImportContext
{
    /** @param array<string, scalar|null> $options */
    public function __construct(
        public string $runId,
        public EntityType $entityType,
        public ?string $externalId = null,
        public ?string $query = null,
        public int $pageSize = 50,
        public array $options = [],
    ) {}
}

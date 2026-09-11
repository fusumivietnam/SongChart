<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Events;

use App\Domain\Catalog\Enums\EntityType;

final readonly class CanonicalEntityChanged
{
    public function __construct(
        public EntityType $entityType,
        public string $entityId,
        public string $fieldName,
    ) {}
}

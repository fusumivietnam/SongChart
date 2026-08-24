<?php

declare(strict_types=1);

namespace App\Contracts\Catalog;

use App\Domain\Catalog\Enums\EntityType;

interface FieldAuthorityPolicy
{
    public function authority(string $sourceKey, EntityType $entityType, string $fieldName): float;
}

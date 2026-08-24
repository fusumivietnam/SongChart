<?php

declare(strict_types=1);

namespace App\Domain\Providers\Mutation\Contracts;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Mutation\DTO\CanonicalMutationResult;
use App\Models\Catalog\MetadataSource;

interface CanonicalMutationAction
{
    public function supports(EntityType $entityType): bool;

    public function mutate(NormalizedProviderEntity $entity, MetadataSource $source): CanonicalMutationResult;
}

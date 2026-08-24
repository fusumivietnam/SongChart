<?php

declare(strict_types=1);

namespace App\Domain\Providers\Mutation\Contracts;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Mutation\DTO\CanonicalMutationResult;

interface CanonicalMutationPipeline
{
    public function apply(NormalizedProviderEntity $entity): CanonicalMutationResult;
}

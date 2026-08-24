<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Contracts;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Identity\DTO\IdentityResolutionResult;

interface ExactIdentityResolver
{
    public function resolve(NormalizedProviderEntity $entity): IdentityResolutionResult;

    public function recordCreatedMatch(NormalizedProviderEntity $entity, string $canonicalEntityId): void;
}

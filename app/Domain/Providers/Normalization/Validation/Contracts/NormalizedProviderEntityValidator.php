<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Validation\Contracts;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Normalization\Validation\DTO\NormalizationValidationResult;

interface NormalizedProviderEntityValidator
{
    public function validate(NormalizedProviderEntity $entity): NormalizationValidationResult;
}

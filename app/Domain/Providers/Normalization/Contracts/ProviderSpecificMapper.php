<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Contracts;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;

interface ProviderSpecificMapper
{
    public function supports(ProviderPayload $payload): bool;

    public function map(ProviderPayload $payload): NormalizedProviderEntity;
}

<?php

declare(strict_types=1);

namespace App\Contracts\Providers\Rate;

use App\Domain\Providers\Rate\DTO\ProviderRatePolicy;

interface ProviderRatePolicyRegistry
{
    public function for(string $providerSlug, string $operation): ProviderRatePolicy;
}

<?php

declare(strict_types=1);

namespace App\Contracts\Providers\Rate;

use App\Domain\Providers\Rate\DTO\ProviderRatePolicy;
use App\Domain\Providers\Rate\DTO\ProviderRateState;

interface ProviderRequestGate
{
    public function await(ProviderRatePolicy $policy): void;

    public function recordCooldown(ProviderRatePolicy $policy, ?int $seconds = null, ?string $reason = null): void;

    public function state(ProviderRatePolicy $policy): ProviderRateState;
}

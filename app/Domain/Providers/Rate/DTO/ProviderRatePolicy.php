<?php

declare(strict_types=1);

namespace App\Domain\Providers\Rate\DTO;

use App\Domain\Providers\Rate\Enums\ProviderRateStrategy;

final readonly class ProviderRatePolicy
{
    public function __construct(
        public string $providerSlug,
        public string $operation,
        public ProviderRateStrategy $strategy,
        public int $minimumIntervalMilliseconds = 0,
        public int $defaultCooldownSeconds = 1,
        public int $maximumCooldownSeconds = 900,
        public int $lockWaitSeconds = 10,
    ) {}
}

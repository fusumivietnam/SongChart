<?php

declare(strict_types=1);

namespace App\Domain\Providers\Rate\DTO;

final readonly class ProviderRateState
{
    public function __construct(
        public string $providerSlug,
        public string $strategy,
        public int $minimumIntervalMilliseconds,
        public ?float $lastRequestStartedAt,
        public ?float $cooldownUntil,
        public int $cooldownRemainingSeconds,
        public ?string $cooldownReason,
    ) {}

    public function status(): string
    {
        return $this->cooldownRemainingSeconds > 0 ? 'cooldown' : 'available';
    }
}

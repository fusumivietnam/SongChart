<?php

declare(strict_types=1);

namespace App\Support\Providers\Rate;

use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Domain\Providers\Rate\DTO\ProviderRatePolicy;
use App\Domain\Providers\Rate\Enums\ProviderRateStrategy;

final class ConfigProviderRatePolicyRegistry implements ProviderRatePolicyRegistry
{
    public function for(string $providerSlug, string $operation): ProviderRatePolicy
    {
        $providerConfig = config('songchart.providers.'.$providerSlug.'.rate', []);
        if (! is_array($providerConfig)) {
            $providerConfig = [];
        }

        $operations = $providerConfig['operations'] ?? [];
        $operationConfig = is_array($operations) && is_array($operations[$operation] ?? null)
            ? $operations[$operation]
            : [];

        $strategy = ProviderRateStrategy::tryFrom((string) ($operationConfig['strategy'] ?? $providerConfig['strategy'] ?? 'none'))
            ?? ProviderRateStrategy::None;

        return new ProviderRatePolicy(
            providerSlug: $providerSlug,
            operation: $operation,
            strategy: $strategy,
            minimumIntervalMilliseconds: max(0, (int) ($operationConfig['minimum_interval_ms'] ?? $providerConfig['minimum_interval_ms'] ?? 0)),
            defaultCooldownSeconds: max(1, (int) ($operationConfig['default_cooldown_seconds'] ?? $providerConfig['default_cooldown_seconds'] ?? 1)),
            maximumCooldownSeconds: max(1, (int) ($operationConfig['maximum_cooldown_seconds'] ?? $providerConfig['maximum_cooldown_seconds'] ?? 900)),
            lockWaitSeconds: max(1, (int) ($operationConfig['lock_wait_seconds'] ?? $providerConfig['lock_wait_seconds'] ?? 10)),
        );
    }
}

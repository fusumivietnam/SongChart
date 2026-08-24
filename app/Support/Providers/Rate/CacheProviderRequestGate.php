<?php

declare(strict_types=1);

namespace App\Support\Providers\Rate;

use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use App\Domain\Providers\Rate\DTO\ProviderRatePolicy;
use App\Domain\Providers\Rate\DTO\ProviderRateState;
use App\Domain\Providers\Rate\Enums\ProviderRateStrategy;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

final class CacheProviderRequestGate implements ProviderRequestGate
{
    public function await(ProviderRatePolicy $policy): void
    {
        $this->throwIfCoolingDown($policy);

        if ($policy->strategy === ProviderRateStrategy::None) {
            return;
        }

        $lock = Cache::lock($this->key($policy->providerSlug, 'global-lock'), max(5, $policy->lockWaitSeconds + 2));

        try {
            $lock->block($policy->lockWaitSeconds, function () use ($policy): void {
                $this->throwIfCoolingDown($policy);

                $lastStartedAt = Cache::get($this->key($policy->providerSlug, 'last-request-started-at'));
                if (is_numeric($lastStartedAt) && $policy->minimumIntervalMilliseconds > 0) {
                    $elapsedMilliseconds = (microtime(true) - (float) $lastStartedAt) * 1000;
                    $remainingMilliseconds = (int) ceil($policy->minimumIntervalMilliseconds - $elapsedMilliseconds);
                    if ($remainingMilliseconds > 0) {
                        usleep($remainingMilliseconds * 1000);
                    }
                }

                Cache::put($this->key($policy->providerSlug, 'last-request-started-at'), microtime(true), 3600);
            });
        } catch (LockTimeoutException $exception) {
            throw new ProviderRequestException(
                message: 'Provider request gate is busy for '.$policy->providerSlug.'.',
                kind: 'request-gate-busy',
                retryable: true,
                retryAfterSeconds: 1,
                previous: $exception,
            );
        }
    }

    public function recordCooldown(ProviderRatePolicy $policy, ?int $seconds = null, ?string $reason = null): void
    {
        $cooldownSeconds = max(1, min($policy->maximumCooldownSeconds, $seconds ?? $policy->defaultCooldownSeconds));
        Cache::put($this->key($policy->providerSlug, 'cooldown-until'), microtime(true) + $cooldownSeconds, $cooldownSeconds + 60);
        Cache::put($this->key($policy->providerSlug, 'cooldown-reason'), $reason ?? 'provider-limit', $cooldownSeconds + 60);
    }

    public function state(ProviderRatePolicy $policy): ProviderRateState
    {
        $lastRequestStartedAt = Cache::get($this->key($policy->providerSlug, 'last-request-started-at'));
        $cooldownUntil = Cache::get($this->key($policy->providerSlug, 'cooldown-until'));
        $cooldownReason = Cache::get($this->key($policy->providerSlug, 'cooldown-reason'));
        $cooldownUntilValue = is_numeric($cooldownUntil) ? (float) $cooldownUntil : null;
        $remaining = $cooldownUntilValue === null ? 0 : max(0, (int) ceil($cooldownUntilValue - microtime(true)));

        return new ProviderRateState(
            providerSlug: $policy->providerSlug,
            strategy: $policy->strategy->value,
            minimumIntervalMilliseconds: $policy->minimumIntervalMilliseconds,
            lastRequestStartedAt: is_numeric($lastRequestStartedAt) ? (float) $lastRequestStartedAt : null,
            cooldownUntil: $cooldownUntilValue,
            cooldownRemainingSeconds: $remaining,
            cooldownReason: is_string($cooldownReason) && $cooldownReason !== '' ? $cooldownReason : null,
        );
    }

    private function throwIfCoolingDown(ProviderRatePolicy $policy): void
    {
        $state = $this->state($policy);
        if ($state->cooldownRemainingSeconds <= 0) {
            return;
        }

        throw new ProviderRequestException(
            message: 'Provider '.$policy->providerSlug.' is cooling down after a temporary service limit.',
            kind: 'cooldown-active',
            retryable: true,
            retryAfterSeconds: $state->cooldownRemainingSeconds,
        );
    }

    private function key(string $providerSlug, string $suffix): string
    {
        return 'provider-rate:'.strtolower($providerSlug).':'.$suffix;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Providers;

use App\Domain\Providers\Enums\ProviderSyncOperation;
use App\Domain\Providers\Enums\ProviderSyncStatus;
use App\Jobs\Providers\CheckProviderHealth;
use App\Models\Provider;
use App\Models\ProviderSyncRun;

final class DispatchProviderHealthChecks
{
    /** @param list<string> $providerSlugs */
    public function handle(array $providerSlugs = [], bool $force = false): int
    {
        $query = Provider::query()->where('is_enabled', true)->orderBy('slug');
        if ($providerSlugs !== []) {
            $query->whereIn('slug', $providerSlugs);
        }
        $dispatched = 0;

        /** @var Provider $provider */
        foreach ($query->cursor() as $provider) {
            if (! $force && $this->hasRecentPendingRun($provider)) {
                continue;
            }
            $run = ProviderSyncRun::query()->create([
                'provider_id' => $provider->getKey(),
                'operation' => ProviderSyncOperation::HealthCheck,
                'status' => ProviderSyncStatus::Queued,
                'started_at' => now(),
            ]);
            CheckProviderHealth::dispatch((string) $provider->getKey(), (string) $run->getKey())
                ->onQueue((string) config('songchart.providers.health.queue', 'provider-health'))->afterCommit();
            $dispatched++;
        }

        return $dispatched;
    }

    private function hasRecentPendingRun(Provider $provider): bool
    {
        $staleAfterMinutes = max(1, (int) config('songchart.providers.health.stale_after_minutes', 15));

        return ProviderSyncRun::query()
            ->where('provider_id', $provider->getKey())
            ->where('operation', ProviderSyncOperation::HealthCheck->value)
            ->whereIn('status', ProviderSyncStatus::pendingValues())
            ->where('started_at', '>=', now()->subMinutes($staleAfterMinutes))
            ->exists();
    }
}

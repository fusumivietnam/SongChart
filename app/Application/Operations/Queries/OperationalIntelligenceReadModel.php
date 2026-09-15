<?php

declare(strict_types=1);

namespace App\Application\Operations\Queries;

use App\Enums\QueueName;
use App\Models\ProviderSyncRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class OperationalIntelligenceReadModel
{
    /** @return array<string, int|float|null> */
    public function evidence(): array
    {
        $databaseProbeMs = null;
        try {
            $started = hrtime(true);
            DB::select('select 1');
            $databaseProbeMs = round((hrtime(true) - $started) / 1_000_000, 2);
        } catch (Throwable) {
            // Explicitly unavailable below.
        }

        $queueDepth = null;
        try {
            $queueDepth = array_sum(array_map(
                static fn (QueueName $queue): int => Queue::size($queue->value),
                QueueName::cases(),
            ));
        } catch (Throwable) {
            // Horizon/queue runtime may be unavailable in this environment.
        }

        $pulseSlowEvents = null;
        try {
            if ((bool) config('pulse.enabled', true) && Schema::hasTable('pulse_entries')) {
                $pulseSlowEvents = DB::table('pulse_entries')
                    ->where('timestamp', '>=', now()->subMinutes(15)->timestamp)
                    ->where('type', 'like', 'slow_%')
                    ->count();
            }
        } catch (Throwable) {
            // Pulse evidence is optional and never a correctness dependency.
        }

        $lastProviderSyncAge = null;
        $lastProviderSync = ProviderSyncRun::query()->max('finished_at');
        if (is_string($lastProviderSync) && $lastProviderSync !== '') {
            $lastProviderSyncAge = CarbonImmutable::parse($lastProviderSync)->diffInMinutes(now());
        }

        return [
            'database_probe_ms' => $databaseProbeMs,
            'queue_depth' => $queueDepth,
            'pulse_slow_events_15m' => $pulseSlowEvents,
            'cache_hit_ratio' => null,
            'provider_sync_failures_24h' => ProviderSyncRun::query()
                ->where('status', 'failed')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'provider_sync_age_minutes' => $lastProviderSyncAge,
            'provider_import_failures_24h' => DB::table('provider_import_runs')
                ->whereIn('status', ['failed', 'completed_with_errors'])
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'quarantined_items' => DB::table('provider_import_items')->where('status', 'quarantined')->count(),
            'open_identity_conflicts' => DB::table('identity_conflict_reviews')->whereIn('status', ['open', 'deferred'])->count(),
            'search_visibility' => null,
        ];
    }
}

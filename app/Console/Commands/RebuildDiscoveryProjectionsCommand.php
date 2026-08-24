<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Discovery\Contracts\DiscoveryProjectionPipeline;
use App\Jobs\Discovery\BuildDiscoveryProjection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class RebuildDiscoveryProjectionsCommand extends Command
{
    protected $signature = 'discovery:rebuild {channel? : Discovery channel ULID} {--all : Rebuild all active channels} {--queue : Dispatch rebuild jobs instead of running inline}';

    protected $description = 'Rebuild bounded Discovery read-model projections.';

    public function handle(DiscoveryProjectionPipeline $pipeline): int
    {
        $channel = $this->argument('channel');
        if ($channel === null && ! $this->option('all')) {
            $this->error('Provide a channel ULID or use --all.');

            return self::INVALID;
        }

        $ids = $channel !== null
            ? [(string) $channel]
            : DB::table('discovery_channels')->where('status', 'active')->orderBy('id')->pluck('id')->map(static fn (mixed $id): string => (string) $id)->all();

        foreach ($ids as $id) {
            if ($this->option('queue')) {
                BuildDiscoveryProjection::dispatch($id);
            } else {
                $projection = $pipeline->rebuild($id);
                $this->line(sprintf('%s: projection revision %d, %d items', $id, $projection->projectionRevision, count($projection->items)));
            }
        }

        $this->info(sprintf('Discovery projection rebuild requested for %d channel(s).', count($ids)));

        return self::SUCCESS;
    }
}

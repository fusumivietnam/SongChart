<?php

declare(strict_types=1);

namespace App\Jobs\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryProjectionPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BuildDiscoveryProjection implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 900;

    public function __construct(public readonly string $channelId)
    {
        $this->onQueue((string) config('songchart.discovery.queue', 'discovery-projections'));
    }

    public function uniqueId(): string
    {
        return $this->channelId;
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [15, 60, 180];
    }

    /** @return list<string> */
    public function tags(): array
    {
        return ['discovery-projection', 'channel:'.$this->channelId];
    }

    public function handle(DiscoveryProjectionPipeline $pipeline): void
    {
        $pipeline->rebuild($this->channelId);
    }
}

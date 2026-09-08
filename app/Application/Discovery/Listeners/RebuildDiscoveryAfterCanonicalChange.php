<?php

declare(strict_types=1);

namespace App\Application\Discovery\Listeners;

use App\Domain\Catalog\Events\CanonicalEntityChanged;
use App\Jobs\Discovery\BuildDiscoveryProjection;
use Illuminate\Support\Facades\DB;

final readonly class RebuildDiscoveryAfterCanonicalChange
{
    public function handle(CanonicalEntityChanged $event): void
    {
        $channelIds = DB::table('discovery_channels')
            ->where('status', 'active')
            ->where('entity_type', $event->entityType->value)
            ->orderBy('id')
            ->pluck('id');

        foreach ($channelIds as $channelId) {
            BuildDiscoveryProjection::dispatch((string) $channelId);
        }
    }
}

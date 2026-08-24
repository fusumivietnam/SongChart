<?php

declare(strict_types=1);

namespace App\Support\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryEditorialStateReader;
use App\Domain\Discovery\DTO\DiscoveryEditorialItem;
use App\Domain\Discovery\DTO\DiscoveryEditorialState;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseDiscoveryEditorialStateReader implements DiscoveryEditorialStateReader
{
    public function forChannel(string $channelId): DiscoveryEditorialState
    {
        $now = now();
        $items = DB::table('discovery_channel_items as items')
            ->join('discovery_channels as channels', 'channels.id', '=', 'items.channel_id')
            ->where('items.channel_id', $channelId)
            ->whereColumn('items.entity_type', 'channels.entity_type')
            ->where(static fn (Builder $query) => $query->whereNull('items.starts_at')->orWhere('items.starts_at', '<=', $now))
            ->where(static fn (Builder $query) => $query->whereNull('items.ends_at')->orWhere('items.ends_at', '>', $now))
            ->orderByRaw('items.position IS NULL')
            ->orderBy('items.position')
            ->orderBy('items.entity_id')
            ->get(['items.entity_id', 'items.position', 'items.pinned'])
            ->map(static fn (object $row): DiscoveryEditorialItem => new DiscoveryEditorialItem(
                (string) $row->entity_id,
                $row->position === null ? null : (int) $row->position,
                (bool) $row->pinned,
            ))
            ->all();

        $excluded = DB::table('discovery_channel_exclusions as exclusions')
            ->join('discovery_channels as channels', 'channels.id', '=', 'exclusions.channel_id')
            ->where('exclusions.channel_id', $channelId)
            ->whereColumn('exclusions.entity_type', 'channels.entity_type')
            ->where(static fn (Builder $query) => $query->whereNull('exclusions.expires_at')->orWhere('exclusions.expires_at', '>', $now))
            ->orderBy('exclusions.entity_id')
            ->pluck('exclusions.entity_id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->all();

        return new DiscoveryEditorialState($items, $excluded);
    }
}

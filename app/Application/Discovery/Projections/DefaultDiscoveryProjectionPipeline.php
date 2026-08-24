<?php

declare(strict_types=1);

namespace App\Application\Discovery\Projections;

use App\Domain\Discovery\Contracts\DiscoveryChannelRepository;
use App\Domain\Discovery\Contracts\DiscoveryEditorialStateReader;
use App\Domain\Discovery\Contracts\DiscoveryEntitySource;
use App\Domain\Discovery\Contracts\DiscoveryProjectionPipeline;
use App\Domain\Discovery\Contracts\DiscoveryProjectionWriter;
use App\Domain\Discovery\Contracts\DiscoveryRuleEngine;
use App\Domain\Discovery\DiscoveryChannel;
use App\Domain\Discovery\DTO\DiscoveryEditorialItem;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\DTO\DiscoveryProjection;
use App\Domain\Discovery\DTO\DiscoveryProjectionItem;
use App\Domain\Discovery\Enums\DiscoveryChannelMode;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class DefaultDiscoveryProjectionPipeline implements DiscoveryProjectionPipeline
{
    public const DEFAULT_BATCH_SIZE = 250;

    public function __construct(
        private readonly DiscoveryChannelRepository $channels,
        private readonly DiscoveryEntitySource $entities,
        private readonly DiscoveryEditorialStateReader $editorial,
        private readonly DiscoveryRuleEngine $rules,
        private readonly DiscoveryProjectionWriter $writer,
        private readonly int $projectionTtlMinutes = 15,
        private readonly int $batchSize = self::DEFAULT_BATCH_SIZE,
    ) {
        if ($this->projectionTtlMinutes < 1) {
            throw new InvalidArgumentException('Discovery projection TTL must be at least one minute.');
        }

        if ($this->batchSize < 1 || $this->batchSize > 1000) {
            throw new InvalidArgumentException('Discovery projection batch size must be between 1 and 1000.');
        }
    }

    public function rebuild(string $channelId): DiscoveryProjection
    {
        $channel = $this->channels->get($channelId);
        $state = $this->editorial->forChannel($channelId);
        $excluded = array_fill_keys($state->excludedEntityIds, true);

        $snapshots = match ($channel->mode) {
            DiscoveryChannelMode::Manual => $this->manual($channel, $state->items, $excluded),
            DiscoveryChannelMode::Derived => $this->derived($channel, $excluded),
            DiscoveryChannelMode::Hybrid => $this->hybrid($channel, $state->items, $excluded),
        };

        $items = [];
        foreach (array_slice($snapshots, 0, $channel->defaultLimit) as $index => $snapshot) {
            $items[] = $this->item($snapshot, $index + 1);
        }

        $generatedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $ttl = $this->projectionTtlMinutes;
        $projection = new DiscoveryProjection(
            $channel->id,
            $channel->revision,
            1,
            $channel->rules->schemaVersion ?? 1,
            $this->entities->version($channel->entityType),
            $generatedAt,
            $generatedAt->add(new DateInterval('PT'.$ttl.'M')),
            $items,
        );

        return $this->writer->append($projection);
    }

    /**
     * @param  list<DiscoveryEditorialItem>  $items
     * @param  array<string, true>  $excluded
     * @return list<DiscoveryEntitySnapshot>
     */
    private function manual(DiscoveryChannel $channel, array $items, array $excluded): array
    {
        $ordered = array_values(array_filter($items, static fn (DiscoveryEditorialItem $item): bool => ! isset($excluded[$item->entityId])));
        usort($ordered, static fn (DiscoveryEditorialItem $left, DiscoveryEditorialItem $right): int => ($left->position ?? 501) <=> ($right->position ?? 501) ?: strcmp($left->entityId, $right->entityId));

        return $this->entities->find($channel->entityType, array_map(static fn (DiscoveryEditorialItem $item): string => $item->entityId, $ordered));
    }

    /**
     * @param  array<string, true>  $excluded
     * @return list<DiscoveryEntitySnapshot>
     */
    private function derived(DiscoveryChannel $channel, array $excluded): array
    {
        if ($channel->rules === null) {
            throw new InvalidArgumentException('Derived discovery projection requires channel rules.');
        }

        $compiled = $this->rules->compile($channel->entityType, $channel->rules);
        $top = [];
        foreach ($this->entities->batches($channel->entityType, $this->batchSize) as $batch) {
            foreach ($batch as $snapshot) {
                if (! isset($excluded[$snapshot->id]) && $compiled->matches($snapshot)) {
                    $top[] = $snapshot;
                }
            }
            $top = array_slice($this->rules->sort($top, $channel->sorts), 0, $channel->defaultLimit);
        }

        return $top;
    }

    /**
     * @param  list<DiscoveryEditorialItem>  $items
     * @param  array<string, true>  $excluded
     * @return list<DiscoveryEntitySnapshot>
     */
    private function hybrid(DiscoveryChannel $channel, array $items, array $excluded): array
    {
        $derived = $this->derived($channel, $excluded);
        $pinnedItems = array_values(array_filter($items, static fn (DiscoveryEditorialItem $item): bool => $item->pinned && ! isset($excluded[$item->entityId])));
        $pinnedSnapshots = $this->entities->find($channel->entityType, array_map(static fn (DiscoveryEditorialItem $item): string => $item->entityId, $pinnedItems));
        $byId = [];
        foreach ($pinnedSnapshots as $snapshot) {
            $byId[$snapshot->id] = $snapshot;
        }
        foreach ($pinnedItems as $pinned) {
            $derived = array_values(array_filter($derived, static fn (DiscoveryEntitySnapshot $snapshot): bool => $snapshot->id !== $pinned->entityId));
            if (! isset($byId[$pinned->entityId])) {
                continue;
            }
            $index = max(0, min(count($derived), ($pinned->position ?? 1) - 1));
            array_splice($derived, $index, 0, [$byId[$pinned->entityId]]);
        }

        return array_slice($derived, 0, $channel->defaultLimit);
    }

    private function item(DiscoveryEntitySnapshot $snapshot, int $rank): DiscoveryProjectionItem
    {
        $title = (string) ($snapshot->value('name') ?? $snapshot->value('title') ?? $snapshot->id);
        $slug = (string) ($snapshot->value('slug') ?? $snapshot->id);
        $subtitle = $snapshot->value('country_code') ?? $snapshot->value('release_type') ?? $snapshot->value('artist_type');

        return new DiscoveryProjectionItem(
            $snapshot->entityType,
            $snapshot->id,
            $rank,
            $title,
            $slug,
            $subtitle === null ? null : (string) $subtitle,
        );
    }
}

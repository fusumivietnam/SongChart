<?php

declare(strict_types=1);

use App\Application\Discovery\Projections\DefaultDiscoveryProjectionPipeline;
use App\Application\Discovery\Rules\DefaultDiscoveryRuleEngine;
use App\Domain\Discovery\Contracts\DiscoveryChannelRepository;
use App\Domain\Discovery\Contracts\DiscoveryEditorialStateReader;
use App\Domain\Discovery\Contracts\DiscoveryEntitySource;
use App\Domain\Discovery\Contracts\DiscoveryProjectionWriter;
use App\Domain\Discovery\DiscoveryChannel;
use App\Domain\Discovery\DTO\DiscoveryEditorialItem;
use App\Domain\Discovery\DTO\DiscoveryEditorialState;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\DTO\DiscoveryProjection;
use App\Domain\Discovery\DTO\DiscoveryRuleCondition;
use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryChannelMode;
use App\Domain\Discovery\Enums\DiscoveryChannelStatus;
use App\Domain\Discovery\Enums\DiscoveryLayout;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use App\Domain\Discovery\Enums\DiscoverySortDirection;
use App\Domain\Discovery\ValueObjects\DiscoveryPublicationWindow;
use App\Support\Discovery\CanonicalDiscoveryFieldRegistry;

it('builds a bounded deterministic derived projection', function (): void {
    $channel = new DiscoveryChannel('01TESTCHANNEL00000000000000', 'new-releases', 'new-releases', 'New releases', null, DiscoverableEntityType::Release, DiscoveryChannelMode::Derived, DiscoveryChannelStatus::Active, new DiscoveryRuleSet('and', [new DiscoveryRuleCondition('country_code', DiscoveryRuleOperator::Equal, 'VN')]), [new DiscoverySort('released_on', DiscoverySortDirection::Descending)], DiscoveryLayout::Grid, 2, new DiscoveryPublicationWindow(null, null));
    $snapshots = [
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Release, '01A', ['title' => 'Older', 'slug' => 'older', 'country_code' => 'VN', 'released_on' => '2026-08-01']),
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Release, '01B', ['title' => 'Other market', 'slug' => 'other', 'country_code' => 'US', 'released_on' => '2026-08-10']),
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Release, '01C', ['title' => 'Newest', 'slug' => 'newest', 'country_code' => 'VN', 'released_on' => '2026-08-09']),
    ];

    $pipeline = projectionPipelineFor($channel, $snapshots, new DiscoveryEditorialState([], []));
    $projection = $pipeline->rebuild($channel->id);

    expect(array_map(static fn ($item): string => $item->entityId, $projection->items))->toBe(['01C', '01A']);
    expect($projection->items[0]->rank)->toBe(1)->and($projection->items[1]->rank)->toBe(2);
});

it('applies exclusions and pinned positions for hybrid channels', function (): void {
    $channel = new DiscoveryChannel('01TESTCHANNEL00000000000001', 'featured-artists', 'featured-artists', 'Featured artists', null, DiscoverableEntityType::Artist, DiscoveryChannelMode::Hybrid, DiscoveryChannelStatus::Active, new DiscoveryRuleSet('and', [new DiscoveryRuleCondition('country_code', DiscoveryRuleOperator::Equal, 'VN')]), [new DiscoverySort('name', DiscoverySortDirection::Ascending)], DiscoveryLayout::Grid, 3, new DiscoveryPublicationWindow(null, null));
    $snapshots = [
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Artist, '01A', ['name' => 'A', 'slug' => 'a', 'country_code' => 'VN']),
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Artist, '01B', ['name' => 'B', 'slug' => 'b', 'country_code' => 'VN']),
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Artist, '01C', ['name' => 'C', 'slug' => 'c', 'country_code' => 'US']),
    ];
    $state = new DiscoveryEditorialState([new DiscoveryEditorialItem('01C', 1, true)], ['01B']);
    $projection = projectionPipelineFor($channel, $snapshots, $state)->rebuild($channel->id);

    expect(array_map(static fn ($item): string => $item->entityId, $projection->items))->toBe(['01C', '01A']);
});

/**
 * @param  list<DiscoveryEntitySnapshot>  $snapshots
 */
function projectionPipelineFor(DiscoveryChannel $channel, array $snapshots, DiscoveryEditorialState $state): DefaultDiscoveryProjectionPipeline
{
    $channels = new class($channel) implements DiscoveryChannelRepository
    {
        public function __construct(private DiscoveryChannel $channel) {}

        public function get(string $id): DiscoveryChannel
        {
            return $this->channel;
        }

        public function save(DiscoveryChannel $channel): void {}

        public function existsByKey(string $key): bool
        {
            return $key === $this->channel->key;
        }
    };
    $source = new class($snapshots) implements DiscoveryEntitySource
    {
        /** @param list<DiscoveryEntitySnapshot> $snapshots */
        public function __construct(private array $snapshots) {}

        public function batches(DiscoverableEntityType $entityType, int $batchSize): iterable
        {
            foreach (array_chunk($this->snapshots, 2) as $batch) {
                yield $batch;
            }
        }

        /** @param list<string> $ids @return list<DiscoveryEntitySnapshot> */
        public function find(DiscoverableEntityType $entityType, array $ids): array
        {
            return array_values(array_filter($this->snapshots, static fn (DiscoveryEntitySnapshot $snapshot): bool => in_array($snapshot->id, $ids, true)));
        }

        public function version(DiscoverableEntityType $entityType): string
        {
            return 'test-source-v1';
        }
    };
    $editorial = new class($state) implements DiscoveryEditorialStateReader
    {
        public function __construct(private DiscoveryEditorialState $state) {}

        public function forChannel(string $channelId): DiscoveryEditorialState
        {
            return $this->state;
        }
    };
    $writer = new class implements DiscoveryProjectionWriter
    {
        public function append(DiscoveryProjection $projection): DiscoveryProjection
        {
            return $projection;
        }
    };

    return new DefaultDiscoveryProjectionPipeline($channels, $source, $editorial, new DefaultDiscoveryRuleEngine(new CanonicalDiscoveryFieldRegistry), $writer);
}

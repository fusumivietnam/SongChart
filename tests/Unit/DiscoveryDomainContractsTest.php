<?php

declare(strict_types=1);

use App\Domain\Discovery\DiscoveryChannel;
use App\Domain\Discovery\DTO\DiscoveryRuleCondition;
use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryChannelMode;
use App\Domain\Discovery\Enums\DiscoveryChannelStatus;
use App\Domain\Discovery\Enums\DiscoveryLayout;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use App\Domain\Discovery\ValueObjects\DiscoveryPublicationWindow;

it('keeps discovery publication windows ordered', function (): void {
    expect(fn () => new DiscoveryPublicationWindow(
        new DateTimeImmutable('2026-08-10T10:00:00+00:00'),
        new DateTimeImmutable('2026-08-10T09:00:00+00:00'),
    ))->toThrow(InvalidArgumentException::class);
});

it('requires derived channels to carry a typed rule set', function (): void {
    expect(fn () => new DiscoveryChannel(
        id: '01k00000000000000000000000',
        key: 'fastest-climbers',
        slug: 'fastest-climbers',
        name: 'Fastest Climbers',
        description: null,
        entityType: DiscoverableEntityType::Recording,
        mode: DiscoveryChannelMode::Derived,
        status: DiscoveryChannelStatus::Draft,
        rules: null,
        sorts: [],
        layout: DiscoveryLayout::RankedList,
        defaultLimit: 20,
        publication: new DiscoveryPublicationWindow(null, null),
    ))->toThrow(InvalidArgumentException::class);
});

it('keeps channel keys immutable and archived channels terminal for direct activation', function (): void {
    $channel = new DiscoveryChannel(
        id: '01k00000000000000000000000',
        key: 'fastest-climbers',
        slug: 'fastest-climbers',
        name: 'Fastest Climbers',
        description: null,
        entityType: DiscoverableEntityType::Recording,
        mode: DiscoveryChannelMode::Derived,
        status: DiscoveryChannelStatus::Draft,
        rules: new DiscoveryRuleSet('and', [new DiscoveryRuleCondition('market', DiscoveryRuleOperator::Equal, 'VN')]),
        sorts: [],
        layout: DiscoveryLayout::RankedList,
        defaultLimit: 20,
        publication: new DiscoveryPublicationWindow(null, null),
    );

    $channel->archive();

    expect($channel->key)->toBe('fastest-climbers')
        ->and($channel->status)->toBe(DiscoveryChannelStatus::Archived)
        ->and(fn () => $channel->activate())->toThrow(InvalidArgumentException::class);
});

it('caps rule conditions and channel limits at contract boundaries', function (): void {
    expect(DiscoveryRuleSet::MAX_CONDITIONS)->toBe(25)
        ->and(DiscoveryChannel::MIN_LIMIT)->toBe(1)
        ->and(DiscoveryChannel::MAX_LIMIT)->toBe(100);
});

<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoveryDomainEventType: string
{
    case ChannelCreated = 'discovery.channel.created';
    case ChannelUpdated = 'discovery.channel.updated';
    case ChannelActivated = 'discovery.channel.activated';
    case ChannelPaused = 'discovery.channel.paused';
    case ChannelArchived = 'discovery.channel.archived';
    case ItemsChanged = 'discovery.channel.items_changed';
    case PlacementChanged = 'discovery.placement.changed';
    case ProjectionRequested = 'discovery.projection.requested';
    case ProjectionBuilt = 'discovery.projection.built';
    case ProjectionFailed = 'discovery.projection.failed';
}

<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoveryCapability: string
{
    case View = 'discovery.view';
    case Create = 'discovery.create';
    case Update = 'discovery.update';
    case Publish = 'discovery.publish';
    case Archive = 'discovery.archive';
    case ManageItems = 'discovery.manage_items';
    case ManagePlacements = 'discovery.manage_placements';
}

<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoveryChannelStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';
}

<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoveryChannelMode: string
{
    case Manual = 'manual';
    case Derived = 'derived';
    case Hybrid = 'hybrid';
}

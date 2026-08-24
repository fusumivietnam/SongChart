<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoveryLayout: string
{
    case Grid = 'grid';
    case Carousel = 'carousel';
    case List = 'list';
    case RankedList = 'ranked_list';
    case CompactGrid = 'compact_grid';
}

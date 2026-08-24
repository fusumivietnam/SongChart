<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoverableEntityType: string
{
    case Artist = 'artist';
    case Recording = 'recording';
    case Release = 'release';
    case Collection = 'collection';
}

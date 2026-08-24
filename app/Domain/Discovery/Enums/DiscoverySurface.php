<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoverySurface: string
{
    case Home = 'home';
    case Discovery = 'discovery';
    case Country = 'country';
    case Artist = 'artist';
    case Recording = 'recording';
    case Release = 'release';
}

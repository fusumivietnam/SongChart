<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoverySortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';
}

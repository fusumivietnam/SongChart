<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderRole: string
{
    case Data = 'data';
    case Destination = 'destination';
    case Service = 'service';
}

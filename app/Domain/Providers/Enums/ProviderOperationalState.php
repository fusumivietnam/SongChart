<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderOperationalState: string
{
    case Disabled = 'disabled';
    case Unapproved = 'unapproved';
    case Misconfigured = 'misconfigured';
    case Degraded = 'degraded';
    case Ready = 'ready';
}

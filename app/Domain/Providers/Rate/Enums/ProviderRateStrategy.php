<?php

declare(strict_types=1);

namespace App\Domain\Providers\Rate\Enums;

enum ProviderRateStrategy: string
{
    case None = 'none';
    case MinimumInterval = 'minimum_interval';
}

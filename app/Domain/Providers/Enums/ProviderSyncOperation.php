<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderSyncOperation: string
{
    case HealthCheck = 'health-check';
}

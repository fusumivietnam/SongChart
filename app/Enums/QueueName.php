<?php

declare(strict_types=1);

namespace App\Enums;

enum QueueName: string
{
    case Critical = 'critical';
    case DiscoveryProjections = 'discovery-projections';
    case ProviderHealth = 'provider-health';
    case ProviderImports = 'provider-imports';
    case ProviderNormalization = 'provider-normalization';
    case Notifications = 'notifications';
    case Default = 'default';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $queue): string => $queue->value, self::cases());
    }
}

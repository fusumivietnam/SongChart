<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderCategory: string
{
    case Music = 'music';
    case Analytics = 'analytics';
    case Observability = 'observability';
    case Security = 'security';
    case Email = 'email';
    case Ai = 'ai';
    case Storage = 'storage';
    case Search = 'search';
    case Authentication = 'authentication';
    case Utility = 'utility';
}

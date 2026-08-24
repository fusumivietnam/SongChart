<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Enums;

enum IdentityMatchMethod: string
{
    case ExistingMatch = 'existing-match';
    case ProviderIdentifier = 'provider-identifier';
    case ExternalIdentifier = 'external-identifier';
    case Created = 'created';
    case IdentifierConflict = 'identifier-conflict';
}

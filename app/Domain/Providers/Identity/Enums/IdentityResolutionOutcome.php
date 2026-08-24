<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Enums;

enum IdentityResolutionOutcome: string
{
    case Matched = 'matched';
    case Unmatched = 'unmatched';
    case Conflict = 'conflict';
}

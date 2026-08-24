<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Review\Enums;

enum IdentityConflictReviewStatus: string
{
    case Open = 'open';
    case Deferred = 'deferred';
    case Resolved = 'resolved';

    public function isTerminal(): bool
    {
        return $this === self::Resolved;
    }
}

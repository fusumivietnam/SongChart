<?php

declare(strict_types=1);

namespace App\Domain\Providers\Mutation\Enums;

enum CanonicalMutationOutcome: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Unchanged = 'unchanged';
    case Skipped = 'skipped';
    case Conflict = 'conflict';
}

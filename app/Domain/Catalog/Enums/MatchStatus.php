<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

enum MatchStatus: string
{
    case Candidate = 'candidate';
    case Matched = 'matched';
    case NeedsReview = 'needs_review';
    case Rejected = 'rejected';
    case Superseded = 'superseded';
}

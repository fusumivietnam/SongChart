<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

enum VerificationState: string
{
    case Unverified = 'unverified';
    case Candidate = 'candidate';
    case Verified = 'verified';
    case Disputed = 'disputed';
    case Rejected = 'rejected';
}

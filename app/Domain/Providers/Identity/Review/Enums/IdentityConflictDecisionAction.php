<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Review\Enums;

enum IdentityConflictDecisionAction: string
{
    case ApproveMatch = 'approve_match';
    case RejectCandidate = 'reject_candidate';
    case KeepSeparate = 'keep_separate';
    case DeferMerge = 'defer_merge';
    case Reopen = 'reopen';
}

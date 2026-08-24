<?php

declare(strict_types=1);

use App\Domain\Providers\Identity\Review\DTO\IdentityConflictDecisionResult;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictDecisionAction;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictReviewStatus;

it('serializes conflict review decisions deterministically', function (): void {
    $result = new IdentityConflictDecisionResult('review-1', IdentityConflictDecisionAction::ApproveMatch, IdentityConflictReviewStatus::Resolved, 'entity-1');

    expect($result->toArray())->toBe([
        'review_id' => 'review-1',
        'action' => 'approve_match',
        'status' => 'resolved',
        'selected_entity_id' => 'entity-1',
    ]);
});

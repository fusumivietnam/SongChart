<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Review\DTO;

use App\Domain\Providers\Identity\Review\Enums\IdentityConflictDecisionAction;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictReviewStatus;

final readonly class IdentityConflictDecisionResult
{
    public function __construct(
        public string $reviewId,
        public IdentityConflictDecisionAction $action,
        public IdentityConflictReviewStatus $status,
        public ?string $selectedEntityId,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'review_id' => $this->reviewId,
            'action' => $this->action->value,
            'status' => $this->status->value,
            'selected_entity_id' => $this->selectedEntityId,
        ];
    }
}

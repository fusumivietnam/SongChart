<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\Review\Contracts;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Identity\Review\DTO\IdentityConflictDecisionResult;
use App\Models\ProviderEntity;
use App\Models\Providers\Identity\IdentityConflictReview;

interface IdentityConflictReviewService
{
    /**
     * @param  list<string>  $candidateEntityIds
     * @param  array<string, mixed>  $evidence
     */
    public function open(ProviderEntity $providerEntity, EntityType $entityType, array $candidateEntityIds, array $evidence = []): IdentityConflictReview;

    public function approveMatch(IdentityConflictReview $review, string $entityId, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult;

    public function rejectCandidate(IdentityConflictReview $review, string $entityId, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult;

    public function keepSeparate(IdentityConflictReview $review, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult;

    public function deferMerge(IdentityConflictReview $review, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult;

    public function reopen(IdentityConflictReview $review, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult;
}

<?php

declare(strict_types=1);

namespace App\Support\Providers\Identity\Review;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\MatchStatus;
use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Domain\Providers\Identity\Review\DTO\IdentityConflictDecisionResult;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictDecisionAction;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictReviewStatus;
use App\Models\Catalog\EntityMatch;
use App\Models\ProviderEntity;
use App\Models\Providers\Identity\IdentityConflictDecision;
use App\Models\Providers\Identity\IdentityConflictReview;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EloquentIdentityConflictReviewService implements IdentityConflictReviewService
{
    /**
     * @param  list<string>  $candidateEntityIds
     * @param  array<string, mixed>  $evidence
     */
    public function open(ProviderEntity $providerEntity, EntityType $entityType, array $candidateEntityIds, array $evidence = []): IdentityConflictReview
    {
        $candidateEntityIds = array_values(array_unique(array_map('strval', $candidateEntityIds)));
        sort($candidateEntityIds);

        if (count($candidateEntityIds) < 2) {
            throw new RuntimeException('An identity conflict review requires at least two candidates.');
        }

        $existing = IdentityConflictReview::query()
            ->where('provider_entity_id', $providerEntity->getKey())
            ->whereIn('status', [IdentityConflictReviewStatus::Open, IdentityConflictReviewStatus::Deferred])
            ->latest('opened_at')
            ->first();

        if ($existing !== null) {
            $existing->forceFill([
                'entity_type' => $entityType,
                'status' => IdentityConflictReviewStatus::Open,
                'candidate_entity_ids' => $candidateEntityIds,
                'evidence' => $evidence,
                'resolved_at' => null,
            ])->save();

            return $existing;
        }

        return IdentityConflictReview::query()->create([
            'provider_entity_id' => $providerEntity->getKey(),
            'entity_type' => $entityType,
            'status' => IdentityConflictReviewStatus::Open,
            'candidate_entity_ids' => $candidateEntityIds,
            'evidence' => $evidence,
            'opened_at' => now(),
        ]);
    }

    public function approveMatch(IdentityConflictReview $review, string $entityId, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult
    {
        return $this->decide($review, IdentityConflictDecisionAction::ApproveMatch, $entityId, $actorId, $rationale, function (IdentityConflictReview $locked) use ($entityId): void {
            $this->assertCandidate($locked, $entityId);

            EntityMatch::query()
                ->where('provider_entity_id', $locked->provider_entity_id)
                ->where('entity_type', $locked->entity_type)
                ->where('entity_id', '!=', $entityId)
                ->whereIn('status', [MatchStatus::NeedsReview, MatchStatus::Candidate])
                ->update(['status' => MatchStatus::Superseded->value, 'active_match_key' => null]);

            $selected = EntityMatch::query()
                ->where('provider_entity_id', $locked->provider_entity_id)
                ->where('entity_type', $locked->entity_type)
                ->where('entity_id', $entityId)
                ->firstOrFail();
            $selected->forceFill([
                'status' => MatchStatus::Matched,
                'match_method' => 'manual-review',
                'confidence' => 1,
            ])->save();

            $locked->forceFill([
                'status' => IdentityConflictReviewStatus::Resolved,
                'resolved_at' => now(),
            ])->save();
        });
    }

    public function rejectCandidate(IdentityConflictReview $review, string $entityId, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult
    {
        return $this->decide($review, IdentityConflictDecisionAction::RejectCandidate, $entityId, $actorId, $rationale, function (IdentityConflictReview $locked) use ($entityId): void {
            $this->assertCandidate($locked, $entityId);

            EntityMatch::query()
                ->where('provider_entity_id', $locked->provider_entity_id)
                ->where('entity_type', $locked->entity_type)
                ->where('entity_id', $entityId)
                ->update(['status' => MatchStatus::Rejected->value, 'active_match_key' => null]);

            $remaining = EntityMatch::query()
                ->where('provider_entity_id', $locked->provider_entity_id)
                ->where('entity_type', $locked->entity_type)
                ->whereIn('status', [MatchStatus::NeedsReview, MatchStatus::Candidate])
                ->count();

            if ($remaining === 0) {
                $locked->forceFill([
                    'status' => IdentityConflictReviewStatus::Resolved,
                    'resolved_at' => now(),
                ])->save();
            }
        });
    }

    public function keepSeparate(IdentityConflictReview $review, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult
    {
        return $this->decide($review, IdentityConflictDecisionAction::KeepSeparate, null, $actorId, $rationale, function (IdentityConflictReview $locked): void {
            EntityMatch::query()
                ->where('provider_entity_id', $locked->provider_entity_id)
                ->where('entity_type', $locked->entity_type)
                ->whereIn('status', [MatchStatus::NeedsReview, MatchStatus::Candidate])
                ->update(['status' => MatchStatus::Rejected->value, 'active_match_key' => null]);

            $locked->forceFill([
                'status' => IdentityConflictReviewStatus::Resolved,
                'resolved_at' => now(),
            ])->save();
        });
    }

    public function deferMerge(IdentityConflictReview $review, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult
    {
        return $this->decide($review, IdentityConflictDecisionAction::DeferMerge, null, $actorId, $rationale, function (IdentityConflictReview $locked): void {
            $locked->forceFill([
                'status' => IdentityConflictReviewStatus::Deferred,
                'resolved_at' => null,
            ])->save();
        });
    }

    public function reopen(IdentityConflictReview $review, ?string $actorId = null, ?string $rationale = null): IdentityConflictDecisionResult
    {
        return DB::transaction(function () use ($review, $actorId, $rationale): IdentityConflictDecisionResult {
            $locked = IdentityConflictReview::query()->lockForUpdate()->findOrFail($review->getKey());
            $status = $locked->getAttribute('status');
            if ($status !== IdentityConflictReviewStatus::Resolved) {
                throw new RuntimeException('Only resolved identity conflict reviews may be reopened.');
            }

            $before = $this->snapshot($locked);
            $locked->forceFill([
                'status' => IdentityConflictReviewStatus::Open,
                'resolved_at' => null,
            ])->save();
            $after = $this->snapshot($locked->refresh());
            $this->recordDecision($locked, IdentityConflictDecisionAction::Reopen, null, $actorId, $rationale, $before, $after);

            return new IdentityConflictDecisionResult((string) $locked->getKey(), IdentityConflictDecisionAction::Reopen, IdentityConflictReviewStatus::Open, null);
        });
    }

    /** @param callable(IdentityConflictReview): void $mutation */
    private function decide(IdentityConflictReview $review, IdentityConflictDecisionAction $action, ?string $selectedEntityId, ?string $actorId, ?string $rationale, callable $mutation): IdentityConflictDecisionResult
    {
        return DB::transaction(function () use ($review, $action, $selectedEntityId, $actorId, $rationale, $mutation): IdentityConflictDecisionResult {
            $locked = IdentityConflictReview::query()->lockForUpdate()->findOrFail($review->getKey());
            $status = $locked->getAttribute('status');
            if ($status === IdentityConflictReviewStatus::Resolved) {
                throw new RuntimeException('Resolved identity conflict reviews are immutable until explicitly reopened.');
            }

            $before = $this->snapshot($locked);
            $mutation($locked);
            $locked->refresh();
            $after = $this->snapshot($locked);
            $this->recordDecision($locked, $action, $selectedEntityId, $actorId, $rationale, $before, $after);

            /** @var IdentityConflictReviewStatus $resolvedStatus */
            $resolvedStatus = $locked->getAttribute('status');

            return new IdentityConflictDecisionResult((string) $locked->getKey(), $action, $resolvedStatus, $selectedEntityId);
        });
    }

    private function assertCandidate(IdentityConflictReview $review, string $entityId): void
    {
        $candidates = array_map('strval', (array) $review->candidate_entity_ids);
        if (! in_array($entityId, $candidates, true)) {
            throw new RuntimeException('Selected entity is not a candidate in this review.');
        }
    }

    /** @return array<string, mixed> */
    private function snapshot(IdentityConflictReview $review): array
    {
        return [
            'status' => $review->getAttribute('status') instanceof IdentityConflictReviewStatus
                ? $review->getAttribute('status')->value
                : (string) $review->getAttribute('status'),
            'candidate_entity_ids' => $review->candidate_entity_ids,
            'matches' => EntityMatch::query()
                ->where('provider_entity_id', $review->provider_entity_id)
                ->where('entity_type', $review->entity_type)
                ->orderBy('entity_id')
                ->get(['entity_id', 'status', 'match_method', 'confidence'])
                ->map(static fn (EntityMatch $match): array => [
                    'entity_id' => (string) $match->entity_id,
                    'status' => $match->getAttribute('status') instanceof MatchStatus ? $match->getAttribute('status')->value : (string) $match->getAttribute('status'),
                    'match_method' => (string) $match->match_method,
                    'confidence' => $match->confidence,
                ])->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function recordDecision(IdentityConflictReview $review, IdentityConflictDecisionAction $action, ?string $selectedEntityId, ?string $actorId, ?string $rationale, array $before, array $after): void
    {
        IdentityConflictDecision::query()->create([
            'identity_conflict_review_id' => $review->getKey(),
            'actor_id' => $actorId,
            'action' => $action,
            'selected_entity_id' => $selectedEntityId,
            'rationale' => $rationale,
            'before_snapshot' => $before,
            'after_snapshot' => $after,
            'created_at' => now(),
        ]);
    }
}

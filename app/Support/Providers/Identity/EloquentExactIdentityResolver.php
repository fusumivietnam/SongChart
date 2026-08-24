<?php

declare(strict_types=1);

namespace App\Support\Providers\Identity;

use App\Domain\Catalog\Enums\MatchStatus;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Identity\Contracts\ExactIdentityResolver;
use App\Domain\Providers\Identity\DTO\IdentityResolutionResult;
use App\Domain\Providers\Identity\Enums\IdentityMatchMethod;
use App\Domain\Providers\Identity\Enums\IdentityResolutionOutcome;
use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Models\Catalog\EntityMatch;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Provider;
use App\Models\ProviderEntity;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EloquentExactIdentityResolver implements ExactIdentityResolver
{
    public function __construct(private readonly IdentityConflictReviewService $conflictReviews) {}

    public function resolve(NormalizedProviderEntity $entity): IdentityResolutionResult
    {
        return DB::transaction(function () use ($entity): IdentityResolutionResult {
            $providerEntity = $this->providerEntity($entity);
            $existing = EntityMatch::query()->where('provider_entity_id', $providerEntity->getKey())
                ->where('entity_type', $entity->entityType)->where('status', MatchStatus::Matched)->first();

            if ($existing !== null) {
                return $this->matched($entity, (string) $existing->entity_id, IdentityMatchMethod::ExistingMatch);
            }

            $providerIdentifier = ExternalIdentifier::query()->where('entity_type', $entity->entityType)
                ->where('namespace', 'provider:'.$entity->providerSlug)->where('value', $entity->externalId)->first();
            if ($providerIdentifier !== null) {
                $entityId = (string) $providerIdentifier->entity_id;
                $this->record($providerEntity, $entity, $entityId, MatchStatus::Matched, IdentityMatchMethod::ProviderIdentifier, [$entityId]);

                return $this->matched($entity, $entityId, IdentityMatchMethod::ProviderIdentifier);
            }

            $candidateIds = [];
            foreach ($entity->identifiers as $identifier) {
                $ids = ExternalIdentifier::query()->where('entity_type', $entity->entityType)
                    ->where('namespace', $identifier->namespace)->where('value', $identifier->value)
                    ->pluck('entity_id')->map(static fn (mixed $id): string => (string) $id)->all();
                $candidateIds = [...$candidateIds, ...$ids];
            }
            $candidateIds = array_values(array_unique($candidateIds));

            if (count($candidateIds) === 1) {
                $this->record($providerEntity, $entity, $candidateIds[0], MatchStatus::Matched, IdentityMatchMethod::ExternalIdentifier, $candidateIds);

                return $this->matched($entity, $candidateIds[0], IdentityMatchMethod::ExternalIdentifier);
            }

            if (count($candidateIds) > 1) {
                foreach ($candidateIds as $candidateId) {
                    $this->record($providerEntity, $entity, $candidateId, MatchStatus::NeedsReview, IdentityMatchMethod::IdentifierConflict, $candidateIds);
                }

                $this->conflictReviews->open(
                    $providerEntity,
                    $entity->entityType,
                    $candidateIds,
                    [
                        'method' => IdentityMatchMethod::IdentifierConflict->value,
                        'identifiers' => array_map(static fn ($identifier): array => $identifier->toArray(), $entity->identifiers),
                    ],
                );

                return new IdentityResolutionResult(IdentityResolutionOutcome::Conflict, $entity->entityType, null, IdentityMatchMethod::IdentifierConflict, $candidateIds);
            }

            return new IdentityResolutionResult(IdentityResolutionOutcome::Unmatched, $entity->entityType, null, IdentityMatchMethod::ExternalIdentifier);
        });
    }

    public function recordCreatedMatch(NormalizedProviderEntity $entity, string $canonicalEntityId): void
    {
        $providerEntity = $this->providerEntity($entity);
        $this->record($providerEntity, $entity, $canonicalEntityId, MatchStatus::Matched, IdentityMatchMethod::Created, [$canonicalEntityId]);
    }

    private function providerEntity(NormalizedProviderEntity $entity): ProviderEntity
    {
        $provider = Provider::query()->where('slug', $entity->providerSlug)->first();
        if ($provider === null) {
            throw new RuntimeException('Provider must exist before identity resolution.');
        }

        return ProviderEntity::query()->firstOrCreate([
            'provider_id' => $provider->getKey(), 'entity_type' => $entity->entityType->value,
            'external_id' => $entity->externalId, 'market' => null,
        ], ['status' => 'active']);
    }

    /** @param list<string> $candidateIds */
    private function record(ProviderEntity $providerEntity, NormalizedProviderEntity $entity, string $entityId, MatchStatus $status, IdentityMatchMethod $method, array $candidateIds): void
    {
        EntityMatch::query()->updateOrCreate([
            'provider_entity_id' => $providerEntity->getKey(), 'entity_type' => $entity->entityType, 'entity_id' => $entityId,
        ], [
            'status' => $status, 'match_method' => $method->value,
            'confidence' => $status === MatchStatus::Matched ? 1 : null,
            'evidence' => json_encode(['identifiers' => array_map(static fn ($identifier): array => $identifier->toArray(), $entity->identifiers), 'candidate_entity_ids' => $candidateIds], JSON_THROW_ON_ERROR),
        ]);
    }

    private function matched(NormalizedProviderEntity $entity, string $entityId, IdentityMatchMethod $method): IdentityResolutionResult
    {
        return new IdentityResolutionResult(IdentityResolutionOutcome::Matched, $entity->entityType, $entityId, $method, [$entityId]);
    }
}

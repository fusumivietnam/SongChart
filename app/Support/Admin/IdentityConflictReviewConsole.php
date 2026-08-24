<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\EntityMatch;
use App\Models\Providers\Identity\IdentityConflictReview;
use App\Support\DomainContracts\DomainContractRegistry;
use BackedEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

final class IdentityConflictReviewConsole
{
    public function __construct(private readonly DomainContractRegistry $contracts) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function index(array $filters): array
    {
        $query = IdentityConflictReview::query()
            ->with('providerEntity.provider:id,name,slug')
            ->withCount('decisions')
            ->latest('opened_at');

        $status = is_string($filters['status'] ?? null) ? trim((string) $filters['status']) : '';
        $entityType = is_string($filters['entity_type'] ?? null) ? trim((string) $filters['entity_type']) : '';
        $provider = is_string($filters['provider'] ?? null) ? trim((string) $filters['provider']) : '';

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($entityType !== '') {
            $query->where('entity_type', $entityType);
        }
        if ($provider !== '') {
            $query->whereHas('providerEntity.provider', static fn ($builder) => $builder->where('slug', $provider));
        }

        /** @var LengthAwarePaginator<int, IdentityConflictReview> $reviews */
        $reviews = $query->paginate(25)->withQueryString();

        return [
            'activeAdminNav' => 'identity-conflicts',
            'title' => 'Xung đột định danh cần rà soát',
            'description' => 'So sánh các ứng viên canonical, xem bằng chứng và đưa ra quyết định có lưu lịch sử.',
            'reviews' => $reviews,
            'filters' => ['status' => $status, 'entity_type' => $entityType, 'provider' => $provider],
            'statuses' => ['open', 'deferred', 'resolved'],
            'entityTypes' => EntityType::cases(),
            'metrics' => [
                ['label' => 'Open', 'value' => IdentityConflictReview::query()->where('status', 'open')->count()],
                ['label' => 'Deferred', 'value' => IdentityConflictReview::query()->where('status', 'deferred')->count()],
                ['label' => 'Resolved', 'value' => IdentityConflictReview::query()->where('status', 'resolved')->count()],
                ['label' => 'Visible', 'value' => $reviews->total()],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function show(string $reviewId): array
    {
        $review = IdentityConflictReview::query()
            ->with(['providerEntity.provider:id,name,slug', 'decisions.actor:id,name,email'])
            ->findOrFail($reviewId);

        $entityType = EntityType::from((string) $review->getRawOriginal('entity_type'));

        $candidateIds = array_map('strval', (array) $review->candidate_entity_ids);
        $modelClass = $entityType->modelClass();
        $displayField = $this->contracts->displayField($entityType);
        $slugField = $this->contracts->slugField($entityType);

        $fields = array_values(array_unique(['id', $displayField, $slugField, 'verification_state']));
        /** @var array<string, Model> $entities */
        $entities = $modelClass::query()->whereIn('id', $candidateIds)->get($fields)->keyBy('id')->all();
        /** @var array<string, EntityMatch> $matches */
        $matches = EntityMatch::query()
            ->where('provider_entity_id', $review->provider_entity_id)
            ->where('entity_type', $entityType->value)
            ->whereIn('entity_id', $candidateIds)
            ->get(['entity_id', 'status', 'match_method', 'confidence', 'evidence'])
            ->keyBy('entity_id')
            ->all();

        $candidates = [];
        foreach ($candidateIds as $candidateId) {
            $entity = $entities[$candidateId] ?? null;
            $match = $matches[$candidateId] ?? null;
            $candidates[] = [
                'id' => $candidateId,
                'label' => $entity instanceof Model ? (string) $entity->getAttribute($displayField) : 'Missing canonical entity',
                'slug' => $entity instanceof Model ? (string) $entity->getAttribute($slugField) : null,
                'verification_state' => $entity instanceof Model ? $this->scalarEnumValue($entity->getAttribute('verification_state')) : null,
                'match_status' => $this->scalarEnumValue($match?->getAttribute('status')),
                'match_method' => $match?->getAttribute('match_method'),
                'confidence' => $match?->getAttribute('confidence'),
                'match_evidence' => $match?->getAttribute('evidence'),
            ];
        }

        return [
            'activeAdminNav' => 'identity-conflicts',
            'title' => 'Rà soát định danh',
            'description' => 'Xem bằng chứng từ nguồn dữ liệu và chọn cách xử lý phù hợp.',
            'review' => $review,
            'entityType' => $entityType,
            'candidates' => $candidates,
        ];
    }

    private function scalarEnumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }
}

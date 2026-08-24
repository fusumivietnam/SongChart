<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Application\Catalog\Queries\EntityPassportReadModel;
use App\Contracts\Search\SearchCatalog;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\Collection;
use App\Models\Catalog\EntityRelationship;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Catalog\PublicEntityUrl;
use App\Support\DomainContracts\DomainContractRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class EloquentSearchCatalog implements SearchCatalog
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly DomainContractRegistry $contracts,
        private readonly EntityPassportReadModel $passports,
    ) {}

    public function search(string $query, string $type = 'all', string $sort = 'relevance', int $page = 1): array
    {
        $needle = trim($query);
        $types = $type === 'all' ? EntityType::cases() : [EntityType::from($type)];
        $all = collect($types)->flatMap(fn (EntityType $entityType): array => $this->searchType($entityType, $needle))->values();
        $counts = ['all' => $all->count()];
        foreach (EntityType::cases() as $entityType) {
            $counts[$entityType->value] = $all->where('type', $entityType->value)->count();
        }
        $items = $type === 'all' ? $all : $all->where('type', $type)->values();
        $items = (match ($sort) {
            'title' => $items->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE),
            'year_desc' => $items->sortByDesc('year'),
            default => $items,
        })->values();
        $total = $items->count();
        $offset = max(0, ($page - 1) * self::PER_PAGE);

        return [
            'items' => $items->slice($offset, self::PER_PAGE)->values()->all(),
            'total' => $total,
            'total_all' => $counts['all'],
            'counts' => $counts,
            'related' => $all->pluck('title')->unique()->take(3)->values()->all(),
            'page' => $page,
            'per_page' => self::PER_PAGE,
            'last_page' => max(1, (int) ceil($total / self::PER_PAGE)),
            'from' => $total === 0 ? 0 : $offset + 1,
            'to' => min($offset + self::PER_PAGE, $total),
        ];
    }

    public function homepage(): array
    {
        $featured = collect(EntityType::cases())
            ->flatMap(fn (EntityType $type): array => $this->searchType($type, ''))
            ->take(3)
            ->values()
            ->all();
        $editorial = Collection::query()->where('visibility', 'public')->orderBy('title')->first();

        return [
            'examples' => collect($featured)->pluck('title')->take(3)->values()->all(),
            'entity_entries' => collect(EntityType::cases())->map(fn (EntityType $type): array => [
                'type' => $type->value,
                'label' => $type->label(),
                'description' => 'Khám phá dữ liệu canonical theo loại thực thể.',
                'example' => collect($featured)->firstWhere('type', $type->value)['title'] ?? $type->label(),
            ])->all(),
            'featured' => $featured,
            'editorial' => $editorial instanceof Collection ? [
                'type' => EntityType::Collection->value,
                'label' => EntityType::Collection->label(),
                'slug' => $editorial->slug,
                'title' => $editorial->title,
                'description' => $editorial->description ?? 'Bộ sưu tập canonical của SongChart.',
                'items' => [],
                'provenance' => 'SongChart canonical catalog',
                'url' => PublicEntityUrl::to(EntityType::Collection, (string) $editorial->slug),
            ] : [
                'type' => EntityType::Collection->value,
                'label' => EntityType::Collection->label(),
                'slug' => 'catalog-coming-soon',
                'title' => 'Catalog đang được hoàn thiện',
                'description' => 'Dữ liệu canonical sẽ xuất hiện sau khi được nhập và xác minh.',
                'items' => [],
                'provenance' => 'SongChart system state',
                'url' => route('collections.index'),
            ],
        ];
    }

    public function find(string $type, string $slug): ?array
    {
        $entityType = EntityType::tryFrom($type);
        if ($entityType === null) {
            return null;
        }
        $model = $entityType->modelClass()::query()->where('slug', $slug)->first();

        return $model instanceof Model ? $this->detail($entityType, $model) : null;
    }

    /** @return list<array<string, mixed>> */
    private function searchType(EntityType $type, string $needle): array
    {
        $titleColumn = $this->contracts->displayField($type);
        /** @var Builder<Model> $query */
        $query = $type->modelClass()::query()->orderBy($titleColumn);
        if ($needle !== '') {
            $query->where($titleColumn, 'like', '%'.$needle.'%');
        }

        return $query->limit(100)->get()->map(fn (Model $model): array => $this->summary($type, $model))->all();
    }

    /** @return array<string, mixed> */
    private function summary(EntityType $type, Model $model): array
    {
        $title = (string) $model->getAttribute($this->contracts->displayField($type));
        $dateField = $this->contracts->dateField($type);
        $releasedOn = $dateField !== null ? $model->getAttribute($dateField) : null;
        $year = $releasedOn instanceof \DateTimeInterface ? (int) $releasedOn->format('Y') : 0;
        $verification = $model->getAttribute('verification_state');
        $verified = $verification === VerificationState::Verified || $verification === VerificationState::Verified->value;

        return [
            'type' => $type->value,
            'label' => $type->label(),
            'slug' => (string) $model->getAttribute($this->contracts->slugField($type)),
            'title' => $title,
            'context' => $type->label().' canonical',
            'meta' => $year > 0 ? $type->label().' · '.$year : $type->label().' · Metadata đang hoàn thiện',
            'year' => $year,
            'verified' => $verified,
            'description' => $this->description($type, $model),
            'url' => PublicEntityUrl::to($type, (string) $model->getAttribute($this->contracts->slugField($type))),
        ];
    }

    private function description(EntityType $type, Model $model): string
    {
        $field = $this->contracts->descriptionField($type);

        if ($field === null) {
            return 'Canonical metadata record in SongChart.';
        }

        return (string) ($model->getAttribute($field) ?? 'Canonical metadata record in SongChart.');
    }

    /** @return array<string, mixed> */
    private function detail(EntityType $type, Model $model): array
    {
        $summary = $this->summary($type, $model);
        $identifiers = ExternalIdentifier::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $model->getKey())
            ->orderByDesc('is_primary')
            ->get(['namespace', 'value'])
            ->map(fn (ExternalIdentifier $identifier): array => ['scheme' => $identifier->namespace, 'value' => $identifier->value])
            ->all();
        $assertions = MetadataAssertion::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $model->getKey())
            ->with('source')
            ->latest('observed_at')
            ->limit(10)
            ->get();

        $sources = [];
        foreach ($assertions as $assertion) {
            $source = $assertion->getRelation('source');
            $verificationAttribute = $assertion->getAttribute('verification_state');
            $verification = $verificationAttribute instanceof VerificationState
                ? $verificationAttribute->value
                : (string) $verificationAttribute;
            $observedAt = $assertion->getAttribute('observed_at');

            $sources[] = [
                'name' => $source instanceof MetadataSource ? $source->name : 'SongChart',
                'status' => $verification,
                'checked_at' => $observedAt instanceof \DateTimeInterface
                    ? $observedAt->format('Y-m-d')
                    : 'Chưa ghi nhận',
            ];
        }

        $relationships = $this->relationships($type, $model);
        $providerDestinations = ProviderDestination::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $model->getKey())
            ->where('review_state', 'approved')
            ->with('provider')
            ->latest('verified_at')
            ->get();

        $providers = [];
        foreach ($providerDestinations as $destination) {
            $providerRelation = $destination->getRelation('provider');
            $provider = $providerRelation instanceof Provider ? $providerRelation : null;
            $lastCheckedAt = $destination->getAttribute('last_checked_at');
            $fresh = $lastCheckedAt instanceof \DateTimeInterface
                && $lastCheckedAt >= now()->subDays(30);
            $url = $destination->getAttribute('url');
            $embeddable = $destination->getAttribute('is_embeddable');

            $providers[] = [
                'key' => $provider !== null ? $provider->slug : '',
                'name' => $provider !== null ? $provider->name : 'Provider',
                'status' => $fresh ? 'available' : 'stale',
                'status_label' => $fresh ? 'Có sẵn' : 'Cần kiểm tra lại',
                'availability_reason' => $fresh ? 'Destination đã được người quản trị xác minh từ metadata provider.' : 'Destination đã quá thời hạn freshness 30 ngày.',
                'compliance_state' => 'approved',
                'url' => is_string($url) ? $url : null,
                'market' => 'Theo khả dụng của provider',
                'checked_at' => $lastCheckedAt instanceof \DateTimeInterface ? $lastCheckedAt->format('Y-m-d') : null,
                'capability_label' => $embeddable === true ? 'Video embeddable' : 'Liên kết ngoài',
                'attribution' => $provider !== null ? $provider->name : 'Provider',
                'action_label' => 'Mở '.($provider !== null ? $provider->name : 'provider'),
            ];
        }

        return array_merge($summary, [
            'eyebrow' => 'Canonical '.$type->value.' identity',
            'facts' => [['label' => 'Loại', 'value' => $type->label()], ['label' => 'Identity', 'value' => (string) $model->getKey()]],
            'identifiers' => $identifiers,
            'relationships' => $relationships,
            'relationship_title' => 'Quan hệ canonical',
            'sources' => $sources,
            'providers' => $providers,
            'passport' => $this->passports->for($type, $model),
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function relationships(EntityType $type, Model $model): array
    {
        $rows = EntityRelationship::query()
            ->where(function (Builder $query) use ($type, $model): void {
                $query->where([
                    'subject_type' => $type->value,
                    'subject_id' => $model->getKey(),
                ])->orWhere(function (Builder $inverse) use ($type, $model): void {
                    $inverse->where([
                        'object_type' => $type->value,
                        'object_id' => $model->getKey(),
                    ]);
                });
            })
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $subjectType = EntityType::from((string) $row->getRawOriginal('subject_type'));
            $objectType = EntityType::from((string) $row->getRawOriginal('object_type'));
            $isSubject = $subjectType === $type && (string) $row->subject_id === (string) $model->getKey();
            $targetType = $isSubject ? $objectType : $subjectType;
            $targetId = $isSubject ? (string) $row->object_id : (string) $row->subject_id;
            $target = $targetType->modelClass()::query()->find($targetId);
            if (! $target instanceof Model) {
                continue;
            }
            $item = $this->summary($targetType, $target);
            $relationshipType = (string) $row->getRawOriginal('relationship_type');
            $item['context'] = str_replace('_', ' ', $relationshipType).' · '.$item['context'];
            $items[] = $item;
        }

        return $items;
    }
}

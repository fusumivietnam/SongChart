<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Application\Catalog\Queries\EntityPassportReadModel;
use App\Application\Catalog\Queries\PublicProviderDestinationReadModel;
use App\Application\Catalog\Queries\PublicRelationshipReadModel;
use App\Contracts\Search\SearchCatalog;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\Collection;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
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
        private readonly PublicRelationshipReadModel $relationships,
        private readonly PublicProviderDestinationReadModel $destinations,
    ) {}

    public function search(string $query, string $type = 'all', string $sort = 'relevance', int $page = 1): array
    {
        $needle = trim($query);
        $all = collect(EntityType::cases())
            ->flatMap(fn (EntityType $entityType): array => $this->searchType($entityType, $needle))
            ->values();

        $counts = ['all' => $all->count()];
        foreach (EntityType::cases() as $entityType) {
            $counts[$entityType->value] = $all->where('type', $entityType->value)->count();
        }

        $items = $type === 'all' ? $all : $all->where('type', $type)->values();
        $items = (match ($sort) {
            'title' => $items->sort(fn (array $left, array $right): int => $this->compareCanonicalTieBreak($left, $right)),
            'year_desc' => $items->sort(function (array $left, array $right): int {
                $year = ((int) $right['year']) <=> ((int) $left['year']);

                return $year !== 0 ? $year : $this->compareCanonicalTieBreak($left, $right);
            }),
            default => $items->sort(function (array $left, array $right): int {
                $rank = ((int) $left['search_rank']) <=> ((int) $right['search_rank']);

                return $rank !== 0 ? $rank : $this->compareCanonicalTieBreak($left, $right);
            }),
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
        $modelClass = $type->modelClass();
        $model = new $modelClass;

        /** @var Builder<Model> $query */
        $query = $modelClass::query();
        if ($needle === '') {
            $query->orderByRaw('LOWER('.$titleColumn.')')->orderBy($model->getKeyName());
        } else {
            $query->whereRaw('LOWER('.$titleColumn.') LIKE LOWER(?)', ['%'.$needle.'%'])
                ->select($model->getTable().'.*')
                ->selectRaw(
                    'CASE WHEN LOWER('.$titleColumn.') = LOWER(?) THEN 0 WHEN LOWER('.$titleColumn.') LIKE LOWER(?) THEN 1 ELSE 2 END AS songchart_search_rank',
                    [$needle, $needle.'%'],
                )
                ->orderBy('songchart_search_rank')
                ->orderByRaw('LOWER('.$titleColumn.')')
                ->orderBy($model->getKeyName());
        }

        return $query->limit(100)->get()->map(fn (Model $result): array => $this->summary($type, $result))->all();
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
        $artistType = $type === EntityType::Artist ? (string) ($model->getAttribute('artist_type') ?? '') : null;
        $rank = $model->getAttributes()['songchart_search_rank'] ?? null;

        return [
            'type' => $type->value,
            'label' => $type === EntityType::Artist && PublicEntityUrl::isGroupArtistType($artistType) ? 'Nhóm nhạc' : $type->label(),
            'artist_type' => $artistType,
            'canonical_id' => (string) $model->getKey(),
            'slug' => (string) $model->getAttribute($this->contracts->slugField($type)),
            'title' => $title,
            'context' => $type->label().' canonical',
            'meta' => $year > 0 ? $type->label().' · '.$year : $type->label().' · Metadata đang hoàn thiện',
            'year' => $year,
            'search_rank' => is_numeric($rank) ? (int) $rank : 0,
            'verified' => $verified,
            'description' => $this->description($type, $model),
            'url' => PublicEntityUrl::to($type, (string) $model->getAttribute($this->contracts->slugField($type)), $artistType),
        ];
    }

    /**
     * @param  array<string,mixed>  $left
     * @param  array<string,mixed>  $right
     */
    private function compareCanonicalTieBreak(array $left, array $right): int
    {
        $title = strnatcasecmp((string) $left['title'], (string) $right['title']);
        if ($title !== 0) {
            return $title;
        }

        $type = strcmp((string) $left['type'], (string) $right['type']);
        if ($type !== 0) {
            return $type;
        }

        return strcmp((string) $left['canonical_id'], (string) $right['canonical_id']);
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

        return array_merge($summary, [
            'eyebrow' => 'Canonical '.$type->value.' identity',
            'facts' => [['label' => 'Loại', 'value' => $type->label()], ['label' => 'Identity', 'value' => (string) $model->getKey()]],
            'identifiers' => $identifiers,
            'relationships' => $this->relationships->for($type, $model),
            'relationship_title' => 'Quan hệ canonical',
            'sources' => $sources,
            'providers' => $this->destinations->for($type, $model),
            'passport' => $this->passports->for($type, $model),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Domain\Catalog\Enums\EntityType;
use App\Support\DomainContracts\DomainContractRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CatalogAdministration
{
    public function __construct(private readonly DomainContractRegistry $contracts) {}

    /** @return array<string, array<string, mixed>> */
    public function definitions(): array
    {
        return [
            'artist' => ['type' => EntityType::Artist, 'label' => 'Nghệ sĩ', 'plural' => 'Nghệ sĩ', 'title' => $this->contracts->displayField(EntityType::Artist), 'secondary' => ['sort_name', 'artist_type', 'country_code']],
            'work' => ['type' => EntityType::Work, 'label' => 'Tác phẩm', 'plural' => 'Tác phẩm', 'title' => $this->contracts->displayField(EntityType::Work), 'secondary' => ['work_type', 'language_code']],
            'recording' => ['type' => EntityType::Recording, 'label' => 'Bản thu', 'plural' => 'Bản thu', 'title' => $this->contracts->displayField(EntityType::Recording), 'secondary' => ['duration_ms', 'is_explicit']],
            'version' => ['type' => EntityType::Version, 'label' => 'Phiên bản', 'plural' => 'Phiên bản', 'title' => $this->contracts->displayField(EntityType::Version), 'secondary' => ['version_type', 'duration_ms']],
            'release_group' => ['type' => EntityType::ReleaseGroup, 'label' => 'Nhóm phát hành', 'plural' => 'Nhóm phát hành', 'title' => $this->contracts->displayField(EntityType::ReleaseGroup), 'secondary' => ['primary_type', 'first_release_date', 'disambiguation']],
            'release' => ['type' => EntityType::Release, 'label' => 'Bản phát hành', 'plural' => 'Bản phát hành', 'title' => $this->contracts->displayField(EntityType::Release), 'secondary' => ['release_type', 'released_on', 'country_code', 'barcode']],
            'collection' => ['type' => EntityType::Collection, 'label' => 'Bộ sưu tập', 'plural' => 'Bộ sưu tập', 'title' => $this->contracts->displayField(EntityType::Collection), 'secondary' => ['visibility', 'description']],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function index(string $type, array $filters): array
    {
        $definition = $this->definition($type);
        $modelClass = $definition['type']->modelClass();
        $query = $modelClass::query();
        $term = trim((string) ($filters['q'] ?? ''));
        $state = (string) ($filters['state'] ?? '');
        $sort = (string) ($filters['sort'] ?? 'title');

        if ($term !== '') {
            $query->where(function ($builder) use ($definition, $term): void {
                $builder->where($definition['title'], 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%');
            });
        }
        if (in_array($state, ['unverified', 'candidate', 'verified', 'disputed', 'rejected'], true)) {
            $query->where('verification_state', $state);
        }

        match ($sort) {
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            default => $query->orderBy($definition['title'])->orderBy('id'),
        };

        /** @var LengthAwarePaginator<int, Model> $entities */
        $entities = $query->paginate(25)->withQueryString();

        return [
            'activeAdminNav' => 'catalog',
            'title' => $definition['plural'],
            'description' => 'Tìm kiếm, lọc và kiểm tra dữ liệu canonical theo từng loại thực thể.',
            'entityType' => $type,
            'definition' => $definition,
            'definitions' => $this->definitions(),
            'entities' => $entities,
            'filters' => compact('term', 'state', 'sort'),
        ];
    }

    /** @return array<string, mixed> */
    public function show(string $type, string $id): array
    {
        $definition = $this->definition($type);
        $modelClass = $definition['type']->modelClass();
        /** @var Model|null $entity */
        $entity = $modelClass::query()->find($id);
        if (! $entity) {
            throw new NotFoundHttpException;
        }

        $enumValue = $definition['type']->value;
        $identifiers = DB::table('external_identifiers')->where('entity_type', $enumValue)->where('entity_id', $id)->orderByDesc('is_primary')->orderBy('namespace')->limit(100)->get();
        $relationships = DB::table('entity_relationships')->where(fn ($q) => $q->where('subject_type', $enumValue)->where('subject_id', $id))->orWhere(fn ($q) => $q->where('object_type', $enumValue)->where('object_id', $id))->latest()->limit(100)->get();
        $conflicts = DB::table('metadata_conflicts')
            ->where('entity_type', $enumValue)
            ->where('entity_id', $id)
            ->latest()
            ->limit(100)
            ->get();

        return [
            'activeAdminNav' => 'catalog',
            'title' => (string) $entity->getAttribute($definition['title']),
            'description' => $definition['label'].' canonical · inspection and governed editing',
            'entityType' => $type,
            'definition' => $definition,
            'entity' => $entity,
            'identifiers' => $identifiers,
            'relationships' => $relationships,
            'conflicts' => $conflicts,
        ];
    }

    /** @return array<string, mixed> */
    private function definition(string $type): array
    {
        return $this->definitions()[$type] ?? throw new NotFoundHttpException;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicCatalogReadModel
{
    /** @return array<string, mixed> */
    public function index(EntityType $type, string $query = ''): array
    {
        if (! in_array($type, [EntityType::Artist, EntityType::Release, EntityType::Recording, EntityType::Work, EntityType::Collection], true)) {
            throw new NotFoundHttpException;
        }

        $modelClass = $type->modelClass();
        $titleColumn = [
            EntityType::Artist->value => 'name',
            EntityType::Release->value => 'title',
            EntityType::Recording->value => 'title',
            EntityType::Work->value => 'title',
            EntityType::Collection->value => 'title',
        ][$type->value];

        /** @var Builder<Model> $builder */
        $builder = $modelClass::query();
        if ($type === EntityType::Collection) {
            $builder->where('visibility', 'public');
        }

        $term = trim($query);
        if ($term !== '') {
            $builder->where(function (Builder $nested) use ($titleColumn, $term): void {
                $nested->where($titleColumn, 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%');
            });
        }

        /** @var LengthAwarePaginator<int, Model> $entities */
        $entities = $builder->orderBy($titleColumn)->orderBy('id')->paginate(24)->withQueryString();

        return [
            'entityType' => $type->value,
            'title' => [
                EntityType::Artist->value => 'Nghệ sĩ',
                EntityType::Release->value => 'Phát hành',
                EntityType::Recording->value => 'Bản thu',
                EntityType::Work->value => 'Tác phẩm',
                EntityType::Collection->value => 'Bộ sưu tập',
            ][$type->value],
            'description' => [
                EntityType::Artist->value => 'Khám phá nghệ sĩ đã có trong catalog canonical của SongChart.',
                EntityType::Release->value => 'Khám phá các edition/phát hành canonical đã nhập từ nguồn metadata và được SongChart chuẩn hóa.',
                EntityType::Recording->value => 'Khám phá các bản thu canonical đã nhập từ MusicBrainz, gồm duration và ISRC evidence khi có.',
                EntityType::Work->value => 'Khám phá tác phẩm/composition canonical được liên kết từ Recording hoặc nhập trực tiếp từ MusicBrainz.',
                EntityType::Collection->value => 'Các bộ sưu tập public do SongChart biên tập và xác minh.',
            ][$type->value],
            'titleColumn' => $titleColumn,
            'entities' => $entities,
            'query' => $term,
        ];
    }
}

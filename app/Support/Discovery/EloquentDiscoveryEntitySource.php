<?php

declare(strict_types=1);

namespace App\Support\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryEntityMapper;
use App\Domain\Discovery\Contracts\DiscoveryEntitySource;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final class EloquentDiscoveryEntitySource implements DiscoveryEntitySource
{
    public function __construct(private readonly DiscoveryEntityMapper $mapper) {}

    public function batches(DiscoverableEntityType $entityType, int $batchSize): iterable
    {
        if ($batchSize < 1 || $batchSize > 1000) {
            throw new InvalidArgumentException('Discovery source batch size must be between 1 and 1000.');
        }

        $afterId = null;
        while (true) {
            $query = $this->query($entityType)->orderBy('id')->limit($batchSize);
            if ($afterId !== null) {
                $query->where('id', '>', $afterId);
            }

            $models = $query->get();
            if ($models->isEmpty()) {
                break;
            }

            $batch = [];
            foreach ($models as $model) {
                $batch[] = $this->mapper->map($model);
            }

            yield $batch;
            $last = $models->last();
            $afterId = (string) $last->getKey();
        }
    }

    /**
     * @param  list<string>  $ids
     * @return list<DiscoveryEntitySnapshot>
     */
    public function find(DiscoverableEntityType $entityType, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $uniqueIds = array_values(array_unique($ids));
        if (count($uniqueIds) > 500) {
            throw new InvalidArgumentException('Discovery source lookup supports at most 500 ids.');
        }

        $byId = [];
        foreach ($this->query($entityType)->whereIn('id', $uniqueIds)->get() as $model) {
            $byId[(string) $model->getKey()] = $this->mapper->map($model);
        }

        $snapshots = [];
        foreach ($uniqueIds as $id) {
            if (isset($byId[$id])) {
                $snapshots[] = $byId[$id];
            }
        }

        return $snapshots;
    }

    public function version(DiscoverableEntityType $entityType): string
    {
        $query = $this->query($entityType);
        $count = (int) (clone $query)->count();
        $latest = (string) ((clone $query)->max('updated_at') ?? 'empty');

        return hash('sha256', $entityType->value.'|'.$count.'|'.$latest);
    }

    /**
     * @return Builder<Artist>|Builder<Collection>|Builder<Recording>|Builder<Release>
     */
    private function query(DiscoverableEntityType $entityType): Builder
    {
        $model = match ($entityType) {
            DiscoverableEntityType::Artist => new Artist,
            DiscoverableEntityType::Recording => new Recording,
            DiscoverableEntityType::Release => new Release,
            DiscoverableEntityType::Collection => new Collection,
        };

        return $model->newQuery();
    }
}

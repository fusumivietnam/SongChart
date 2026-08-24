<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class CatalogEntityResolver
{
    public function exists(EntityType $type, string $id): bool
    {
        return $type->modelClass()::query()->whereKey($id)->exists();
    }

    public function resolve(EntityType $type, string $id): Model
    {
        $model = $type->modelClass()::query()->find($id);

        if (! $model instanceof Model) {
            throw (new ModelNotFoundException)->setModel($type->modelClass(), [$id]);
        }

        return $model;
    }
}

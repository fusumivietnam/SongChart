<?php

declare(strict_types=1);

namespace App\Application\UserLibrary;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\User;
use App\Models\UserSavedEntity;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class SaveEntity
{
    public function save(User $user, EntityType $type, string $entityId): UserSavedEntity
    {
        $modelClass = $type->modelClass();

        if (! $modelClass::query()->whereKey($entityId)->exists()) {
            throw (new ModelNotFoundException)->setModel($modelClass, [$entityId]);
        }

        return UserSavedEntity::query()->firstOrCreate([
            'user_id' => $user->getKey(),
            'entity_type' => $type->value,
            'entity_id' => $entityId,
        ]);
    }

    public function remove(User $user, EntityType $type, string $entityId): void
    {
        UserSavedEntity::query()
            ->where('user_id', $user->getKey())
            ->where('entity_type', $type->value)
            ->where('entity_id', $entityId)
            ->delete();
    }
}

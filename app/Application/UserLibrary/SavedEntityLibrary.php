<?php

declare(strict_types=1);

namespace App\Application\UserLibrary;

use App\Models\User;
use App\Models\UserSavedEntity;
use App\Support\Catalog\PublicEntityUrl;
use App\Support\DomainContracts\DomainContractRegistry;
use Illuminate\Database\Eloquent\Model;

final class SavedEntityLibrary
{
    public function __construct(private readonly DomainContractRegistry $contracts) {}

    /** @return list<array<string, mixed>> */
    public function forUser(User $user): array
    {
        return $user->savedEntities()
            ->latest('created_at')
            ->get()
            ->map(function (UserSavedEntity $saved): array {
                $type = $saved->entity_type;
                $modelClass = $type->modelClass();
                $entity = $modelClass::query()->find($saved->entity_id);

                if (! $entity instanceof Model) {
                    return [
                        'type' => $type,
                        'entity_id' => (string) $saved->entity_id,
                        'available' => false,
                        'title' => 'Thực thể không còn khả dụng',
                        'url' => null,
                    ];
                }

                $slug = (string) $entity->getAttribute($this->contracts->slugField($type));
                $title = (string) $entity->getAttribute($this->contracts->displayField($type));
                $artistType = $type->value === 'artist' ? (string) ($entity->getAttribute('artist_type') ?? '') : null;

                return [
                    'type' => $type,
                    'entity_id' => (string) $saved->entity_id,
                    'available' => $slug !== '',
                    'title' => $title,
                    'url' => $slug !== '' ? PublicEntityUrl::to($type, $slug, $artistType) : null,
                ];
            })
            ->values()
            ->all();
    }
}

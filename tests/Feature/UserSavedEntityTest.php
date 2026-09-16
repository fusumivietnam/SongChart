<?php

declare(strict_types=1);

use App\Application\UserLibrary\SaveEntity;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Artist;
use App\Models\User;
use App\Models\UserSavedEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

uses(RefreshDatabase::class);

it('saves a canonical entity idempotently for one user', function (): void {
    $user = User::factory()->create();
    $artist = Artist::factory()->create();
    $action = app(SaveEntity::class);

    $first = $action->save($user, EntityType::Artist, (string) $artist->getKey());
    $second = $action->save($user, EntityType::Artist, (string) $artist->getKey());

    expect($second->getKey())->toBe($first->getKey())
        ->and(UserSavedEntity::query()->count())->toBe(1)
        ->and($first->entity_type)->toBe(EntityType::Artist)
        ->and($first->entity_id)->toBe((string) $artist->getKey());
});

it('keeps removal scoped to the owning user', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $artist = Artist::factory()->create();
    $action = app(SaveEntity::class);

    $saved = $action->save($owner, EntityType::Artist, (string) $artist->getKey());
    $action->remove($other, EntityType::Artist, (string) $artist->getKey());

    expect(UserSavedEntity::query()->find($saved->getKey()))->not->toBeNull();

    $action->remove($owner, EntityType::Artist, (string) $artist->getKey());

    expect(UserSavedEntity::query()->find($saved->getKey()))->toBeNull();
});

it('deletes saved state when the owning account is deleted', function (): void {
    $user = User::factory()->create();
    $artist = Artist::factory()->create();
    $saved = app(SaveEntity::class)->save($user, EntityType::Artist, (string) $artist->getKey());

    $user->delete();

    expect(UserSavedEntity::query()->find($saved->getKey()))->toBeNull();
});

it('refuses to save an entity that is not canonical', function (): void {
    $user = User::factory()->create();

    expect(fn () => app(SaveEntity::class)->save($user, EntityType::Artist, '01K5ZZZZZZZZZZZZZZZZZZZZZZ'))
        ->toThrow(ModelNotFoundException::class);
});

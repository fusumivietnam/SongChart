<?php

declare(strict_types=1);

use App\Application\UserLibrary\SaveEntity;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Artist;
use App\Models\User;
use App\Models\UserSavedEntity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('saves a canonical entity idempotently for one user', function (): void {
    $user = User::factory()->create();
    $artist = Artist::factory()->create();
    $action = app(SaveEntity::class);

    $first = $action->add($user, EntityType::Artist, (string) $artist->getKey());
    $second = $action->add($user, EntityType::Artist, (string) $artist->getKey());

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

    $saved = $action->add($owner, EntityType::Artist, (string) $artist->getKey());
    $action->remove($other, EntityType::Artist, (string) $artist->getKey());

    expect(UserSavedEntity::query()->find($saved->getKey()))->not->toBeNull();

    $action->remove($owner, EntityType::Artist, (string) $artist->getKey());

    expect(UserSavedEntity::query()->find($saved->getKey()))->toBeNull();
});

it('deletes saved state when the owning account is deleted', function (): void {
    $user = User::factory()->create();
    $artist = Artist::factory()->create();
    $saved = app(SaveEntity::class)->add($user, EntityType::Artist, (string) $artist->getKey());

    $user->delete();

    expect(UserSavedEntity::query()->find($saved->getKey()))->toBeNull();
});

it('refuses to save an entity that is not canonical', function (): void {
    $user = User::factory()->create();

    expect(fn () => app(SaveEntity::class)->add($user, EntityType::Artist, '01K5ZZZZZZZZZZZZZZZZZZZZZZ'))
        ->toThrow(ModelNotFoundException::class);
});

it('requires an authenticated verified active account for the saved library', function (): void {
    $this->get(route('account.saved.index'))
        ->assertRedirect(route('login'));
});

it('stores the same canonical entity only once through the account route', function (): void {
    $user = User::factory()->create();
    $artist = Artist::factory()->create();
    $parameters = ['type' => EntityType::Artist->value, 'id' => (string) $artist->getKey()];

    $this->actingAs($user)->post(route('account.saved.store', $parameters))->assertRedirect();
    $this->actingAs($user)->post(route('account.saved.store', $parameters))->assertRedirect();

    expect(UserSavedEntity::query()
        ->where('user_id', $user->getKey())
        ->where('entity_type', EntityType::Artist->value)
        ->where('entity_id', $artist->getKey())
        ->count())->toBe(1);
});

it('shows only the current users saved canonical entities', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $visible = Artist::factory()->create(['name' => 'Visible Saved Artist']);
    $hidden = Artist::factory()->create(['name' => 'Other Saved Artist']);

    app(SaveEntity::class)->add($user, EntityType::Artist, (string) $visible->getKey());
    app(SaveEntity::class)->add($other, EntityType::Artist, (string) $hidden->getKey());

    $this->actingAs($user)
        ->get(route('account.saved.index'))
        ->assertOk()
        ->assertSee('Visible Saved Artist')
        ->assertDontSee('Other Saved Artist');
});

it('does not let one account remove another accounts saved entity', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $artist = Artist::factory()->create();
    $saved = app(SaveEntity::class)->add($owner, EntityType::Artist, (string) $artist->getKey());

    $this->actingAs($other)
        ->delete(route('account.saved.destroy', [
            'type' => EntityType::Artist->value,
            'id' => (string) $artist->getKey(),
        ]))
        ->assertRedirect();

    expect(UserSavedEntity::query()->find($saved->getKey()))->not->toBeNull();
});

it('shows the save entry point only to authenticated users on entity detail', function (): void {
    $artist = Artist::factory()->create(['slug' => 'saved-entry-point-artist']);

    $this->get(route('artists.show', ['slug' => $artist->slug]))
        ->assertOk()
        ->assertDontSee('Lưu vào tài khoản');

    $this->actingAs(User::factory()->create())
        ->get(route('artists.show', ['slug' => $artist->slug]))
        ->assertOk()
        ->assertSee('Lưu vào tài khoản');
});

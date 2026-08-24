<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\CollectionItem;
use App\Models\Catalog\EntityMatch;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataConflict;
use App\Models\Catalog\MetadataSource;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseTrack;
use App\Models\Catalog\Work;
use App\Models\Provider;
use App\Models\ProviderEntity;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows only one active matched canonical identity per provider entity', function (): void {
    $provider = Provider::query()->create(['name' => 'Fixture', 'slug' => 'fixture-provider', 'category' => 'music', 'status' => 'approved', 'is_enabled' => false]);
    $providerEntity = ProviderEntity::query()->create(['provider_id' => $provider->id, 'entity_type' => 'artist', 'external_id' => 'fixture-1', 'status' => 'active']);
    $first = Artist::factory()->create();
    $second = Artist::factory()->create();

    EntityMatch::query()->create(['provider_entity_id' => $providerEntity->id, 'entity_type' => 'artist', 'entity_id' => $first->id, 'status' => 'matched']);

    expect(fn () => DB::transaction(fn () => EntityMatch::query()->create(['provider_entity_id' => $providerEntity->id, 'entity_type' => 'artist', 'entity_id' => $second->id, 'status' => 'matched'])))
        ->toThrow(QueryException::class);

    EntityMatch::query()->create(['provider_entity_id' => $providerEntity->id, 'entity_type' => 'artist', 'entity_id' => $second->id, 'status' => 'candidate']);
    expect(EntityMatch::query()->where('provider_entity_id', $providerEntity->id)->count())->toBe(2);
});

it('normalizes metadata conflict pairs and rejects self conflicts', function (): void {
    $source = MetadataSource::factory()->create();
    $release = Release::factory()->create();
    $a = MetadataAssertion::query()->create(['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'value' => ['date' => '2020-01-01'], 'value_fingerprint' => hash('sha256', 'a'), 'metadata_source_id' => $source->id, 'observed_at' => now()]);
    $b = MetadataAssertion::query()->create(['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'value' => ['date' => '2020-01-02'], 'value_fingerprint' => hash('sha256', 'b'), 'metadata_source_id' => $source->id, 'observed_at' => now()]);

    MetadataConflict::query()->create(['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'left_assertion_id' => $a->id, 'right_assertion_id' => $b->id]);

    expect(fn () => DB::transaction(fn () => MetadataConflict::query()->create(['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'left_assertion_id' => $b->id, 'right_assertion_id' => $a->id])))
        ->toThrow(QueryException::class)
        ->and(fn () => MetadataConflict::query()->create(['entity_type' => 'release', 'entity_id' => $release->id, 'field_name' => 'released_on', 'left_assertion_id' => $a->id, 'right_assertion_id' => $a->id]))
        ->toThrow(InvalidArgumentException::class);
});

it('enforces catalog collection and relationship deletion constraints', function (): void {
    $work = Work::factory()->create();
    $recording = Recording::factory()->create(['work_id' => $work->id]);
    $release = Release::factory()->create();
    $collection = Collection::factory()->create();

    ReleaseTrack::query()->create(['release_id' => $release->id, 'recording_id' => $recording->id, 'disc_number' => 1, 'track_number' => 1]);
    CollectionItem::query()->create(['collection_id' => $collection->id, 'entity_type' => 'recording', 'entity_id' => $recording->id, 'position' => 1]);

    expect(fn () => DB::transaction(fn () => CollectionItem::query()->create(['collection_id' => $collection->id, 'entity_type' => 'release', 'entity_id' => $release->id, 'position' => 1])))
        ->toThrow(QueryException::class)
        ->and(fn () => DB::transaction(fn () => $recording->forceDelete()))->toThrow(QueryException::class);

    $work->forceDelete();
    expect($recording->fresh()?->work_id)->toBeNull();
});

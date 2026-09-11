<?php

declare(strict_types=1);

use App\Models\Catalog\Collection;
use App\Models\Catalog\CollectionItem;
use App\Models\Catalog\Recording;
use App\Support\DomainContracts\PolymorphicReferenceIntegrity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('accepts registered canonical polymorphic references that resolve to existing rows', function (): void {
    $collection = Collection::factory()->create();
    $recording = Recording::factory()->create();

    CollectionItem::query()->create([
        'collection_id' => $collection->id,
        'entity_type' => 'recording',
        'entity_id' => $recording->id,
        'position' => 1,
    ]);

    $result = app(PolymorphicReferenceIntegrity::class)->scan();

    expect($result['checked'])->toBeGreaterThanOrEqual(1)
        ->and($result['failures'])->toBe([]);
});

it('fails closed when a canonical polymorphic reference points to a missing entity', function (): void {
    $collection = Collection::factory()->create();
    $missingRecording = strtolower((string) Str::ulid());

    CollectionItem::query()->create([
        'collection_id' => $collection->id,
        'entity_type' => 'recording',
        'entity_id' => $missingRecording,
        'position' => 1,
    ]);

    $result = app(PolymorphicReferenceIntegrity::class)->scan();

    expect($result['failures'])->toContain([
        'table' => 'collection_items',
        'type_column' => 'entity_type',
        'id_column' => 'entity_id',
        'entity_type' => 'recording',
        'entity_id' => $missingRecording,
        'reason' => 'missing_entity_row',
    ]);
});

it('fails closed when raw persistence contains an unknown polymorphic entity type', function (): void {
    $collection = Collection::factory()->create();
    $unknownEntityId = strtolower((string) Str::ulid());
    $timestamp = now();

    DB::table('collection_items')->insert([
        'id' => strtolower((string) Str::ulid()),
        'collection_id' => $collection->id,
        'entity_type' => 'provider_song_guess',
        'entity_id' => $unknownEntityId,
        'position' => 1,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
    ]);

    $result = app(PolymorphicReferenceIntegrity::class)->scan();

    expect($result['failures'])->toContain([
        'table' => 'collection_items',
        'type_column' => 'entity_type',
        'id_column' => 'entity_id',
        'entity_type' => 'provider_song_guess',
        'entity_id' => $unknownEntityId,
        'reason' => 'unknown_entity_type',
    ]);
});

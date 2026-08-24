<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use App\Models\Catalog\EntityMatch;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataConflict;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseTrack;
use App\Models\Catalog\Work;
use Database\Seeders\CatalogFixtureSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('creates canonical entities with independent ulid identities', function (): void {
    $artist = Artist::factory()->create();
    $work = Work::factory()->create();
    $recording = Recording::factory()->create(['work_id' => $work->id]);
    $release = Release::factory()->create();

    expect($artist->id)->toHaveLength(26)
        ->and($recording->work_id)->toBe($work->id)
        ->and($release->id)->not->toBe($recording->id);
});

it('enforces release track positions and external identifier uniqueness', function (): void {
    $recording = Recording::factory()->create();
    $release = Release::factory()->create();

    ReleaseTrack::query()->create(['release_id' => $release->id, 'recording_id' => $recording->id, 'disc_number' => 1, 'track_number' => 1]);

    expect(fn () => DB::transaction(fn () => ReleaseTrack::query()->create(['release_id' => $release->id, 'recording_id' => Recording::factory()->create()->id, 'disc_number' => 1, 'track_number' => 1])))
        ->toThrow(QueryException::class);

    ExternalIdentifier::query()->create(['entity_type' => 'recording', 'entity_id' => $recording->id, 'namespace' => 'isrc', 'value' => 'GBAYE9701286']);
    expect(fn () => DB::transaction(fn () => ExternalIdentifier::query()->create(['entity_type' => 'recording', 'entity_id' => Recording::factory()->create()->id, 'namespace' => 'isrc', 'value' => 'GBAYE9701286'])))
        ->toThrow(QueryException::class);
});

it('seeds deterministic provenance conflicts and provider matches idempotently', function (): void {
    $this->seed(CatalogFixtureSeeder::class);
    $this->seed(CatalogFixtureSeeder::class);

    expect(Artist::query()->where('slug', 'radiohead')->count())->toBe(1)
        ->and(MetadataAssertion::query()->where('field_name', 'released_on')->count())->toBe(2)
        ->and(MetadataConflict::query()->count())->toBe(1)
        ->and(EntityMatch::query()->where('status', 'matched')->count())->toBe(1)
        ->and(DB::table('artist_recording')->count())->toBe(1);
});

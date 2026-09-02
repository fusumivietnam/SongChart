<?php

declare(strict_types=1);

use App\Application\Catalog\Queries\EntityPassportReadModel;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Enums\ProviderCategory;
use App\Models\Catalog\Artist;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds one identity bridge and a provider-aware enrichment plan without mutating canonical data', function (): void {
    $artist = Artist::factory()->create([
        'name' => 'BLACKPINK',
        'artist_type' => 'group',
        'country_code' => null,
    ]);

    Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => ProviderCategory::Music->value,
        'status' => 'approved',
        'is_enabled' => true,
    ]);
    Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => ProviderCategory::Music->value,
        'status' => 'approved',
        'is_enabled' => false,
    ]);

    $source = MetadataSource::query()->create([
        'key' => 'provider:musicbrainz',
        'name' => 'MusicBrainz',
        'source_type' => 'provider-import',
    ]);

    foreach ([
        'name' => ['value' => 'BLACKPINK'],
        'artist_type' => ['value' => 'group'],
    ] as $field => $value) {
        MetadataAssertion::query()->create([
            'entity_type' => EntityType::Artist,
            'entity_id' => (string) $artist->getKey(),
            'field_name' => $field,
            'value' => $value,
            'value_fingerprint' => hash('sha256', json_encode($value, JSON_THROW_ON_ERROR)),
            'metadata_source_id' => $source->getKey(),
            'verification_state' => 'candidate',
            'confidence' => 0.95,
            'observed_at' => now(),
        ]);
    }

    ExternalIdentifier::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => (string) $artist->getKey(),
        'namespace' => 'provider:musicbrainz',
        'value' => '859d0860-d480-4efd-970c-c05d5f1776b8',
        'metadata_source_id' => $source->getKey(),
        'is_primary' => true,
        'verification_state' => 'verified',
    ]);

    $passport = app(EntityPassportReadModel::class)->for(EntityType::Artist, $artist);

    expect($passport['identity_bridge']['connected_source_count'])->toBe(1)
        ->and($passport['identity_bridge']['identifiers'])->toHaveCount(1)
        ->and($passport['enrichment_plan']['completeness'])->toBe(75)
        ->and($passport['enrichment_plan']['needs'])->toHaveCount(1)
        ->and($passport['enrichment_plan']['needs'][0]['key'])->toBe('country_code')
        ->and($passport['enrichment_plan']['needs'][0]['provider'])->toBe('musicbrainz')
        ->and($passport['enrichment_plan']['needs'][0]['provider_enabled'])->toBeTrue()
        ->and($artist->fresh()->country_code)->toBeNull();
});

<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use App\Support\Catalog\Fusion\CanonicalFieldResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('selects field evidence deterministically from source authority confidence and freshness', function (): void {
    $musicbrainz = MetadataSource::query()->create([
        'key' => 'provider:musicbrainz',
        'name' => 'MusicBrainz',
        'source_type' => 'provider-import',
    ]);
    $youtube = MetadataSource::query()->create([
        'key' => 'provider:youtube',
        'name' => 'YouTube',
        'source_type' => 'provider-destination',
    ]);

    $entityId = strtolower((string) Str::ulid());
    MetadataAssertion::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => $entityId,
        'field_name' => 'name',
        'value' => ['value' => 'BLACKPINK'],
        'value_fingerprint' => hash('sha256', 'blackpink'),
        'metadata_source_id' => $musicbrainz->getKey(),
        'verification_state' => VerificationState::Candidate,
        'confidence' => 0.90,
        'observed_at' => now(),
    ]);
    MetadataAssertion::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => $entityId,
        'field_name' => 'name',
        'value' => ['value' => 'Black Pink'],
        'value_fingerprint' => hash('sha256', 'black-pink'),
        'metadata_source_id' => $youtube->getKey(),
        'verification_state' => VerificationState::Candidate,
        'confidence' => 1.0,
        'observed_at' => now(),
    ]);

    $resolution = app(CanonicalFieldResolver::class)->resolve(EntityType::Artist, $entityId, 'name');

    expect($resolution->selected?->sourceKey)->toBe('provider:musicbrainz')
        ->and($resolution->selected?->value)->toBe(['value' => 'BLACKPINK'])
        ->and($resolution->hasConflict)->toBeTrue()
        ->and($resolution->evidence)->toHaveCount(2);
});

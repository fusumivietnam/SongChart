<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\DTO\NormalizedAvailability;
use App\Domain\Providers\Normalization\DTO\NormalizedClassification;
use App\Domain\Providers\Normalization\DTO\NormalizedDestination;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\DTO\NormalizedMediaAsset;
use App\Domain\Providers\Normalization\DTO\NormalizedMetricObservation;
use App\Domain\Providers\Normalization\DTO\NormalizedRelationship;
use App\Domain\Providers\Normalization\Enums\FieldPresence;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

it('distinguishes all provider field presence states', function (): void {
    expect(ProviderField::missing()->presence)->toBe(FieldPresence::Missing)
        ->and(ProviderField::unknown()->presence)->toBe(FieldPresence::Unknown)
        ->and(ProviderField::explicitNull()->presence)->toBe(FieldPresence::ExplicitNull)
        ->and(ProviderField::provided('value')->presence)->toBe(FieldPresence::Provided)
        ->and(ProviderField::explicitNull()->toArray())->toBe(['presence' => 'explicit_null'])
        ->and(ProviderField::provided('value')->toArray())->toBe(['presence' => 'provided', 'value' => 'value']);
});

it('rejects null as a provided value', function (): void {
    expect(fn () => ProviderField::provided(null))->toThrow(InvalidArgumentException::class);
});

it('serializes typed artist identifiers and relationships deterministically', function (): void {
    $artist = new NormalizedArtist(
        ProviderField::provided('Radiohead'),
        ProviderField::provided('Radiohead'),
        ProviderField::missing(),
        ProviderField::provided('GB'),
        ProviderField::unknown(),
        ProviderField::explicitNull(),
        ProviderField::provided(false),
    );

    $entity = new NormalizedProviderEntity(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'mbid-artist',
        data: $artist,
        identifiers: [new NormalizedIdentifier('musicbrainz_artist', 'mbid-artist')],
        relationships: [new NormalizedRelationship('member-of', EntityType::Artist, 'mbid-group')],
        normalizerVersion: 'musicbrainz-v1',
    );

    expect($entity->toArray()['fields']['name'])->toBe(['presence' => 'provided', 'value' => 'Radiohead'])
        ->and($entity->toArray()['fields']['endDate'])->toBe(['presence' => 'explicit_null'])
        ->and($entity->toArray()['identifiers'][0]['namespace'])->toBe('musicbrainz_artist')
        ->and($entity->toArray()['relationships'][0]['target_entity_type'])->toBe('artist');
});

it('keeps rich provider evidence separated from canonical scalar fields', function (): void {
    $entity = new NormalizedProviderEntity(
        providerSlug: 'youtube',
        entityType: EntityType::Artist,
        externalId: 'channel-1',
        data: new NormalizedArtist(
            name: ProviderField::provided('Artist'),
            sortName: ProviderField::missing(),
            disambiguation: ProviderField::missing(),
            countryCode: ProviderField::missing(),
            beginDate: ProviderField::missing(),
            endDate: ProviderField::missing(),
            ended: ProviderField::missing(),
        ),
        mediaAssets: [new NormalizedMediaAsset('official_video', 'video-1', 'https://youtube.com/watch?v=video-1', embeddable: true)],
        destinations: [new NormalizedDestination('channel', 'https://youtube.com/@artist', 'channel-1')],
        availability: [new NormalizedAvailability('VN', true)],
        classifications: [new NormalizedClassification('genre', 'pop', 0.8)],
        metrics: [new NormalizedMetricObservation('view_count', 1200, '2026-08-26T00:00:00+07:00')],
    );

    $serialized = $entity->toArray();

    expect($serialized['media_assets'][0]['resource_id'])->toBe('video-1')
        ->and($serialized['destinations'][0]['kind'])->toBe('channel')
        ->and($serialized['availability'][0]['market'])->toBe('VN')
        ->and($serialized['classifications'][0]['term'])->toBe('pop')
        ->and($serialized['metrics'][0]['metric'])->toBe('view_count')
        ->and($serialized['fields'])->not->toHaveKey('view_count');
});

it('rejects an envelope whose entity type differs from its typed data', function (): void {
    $artist = new NormalizedArtist(
        ProviderField::provided('Artist'),
        ProviderField::missing(),
        ProviderField::missing(),
        ProviderField::missing(),
        ProviderField::missing(),
        ProviderField::missing(),
        ProviderField::missing(),
    );

    expect(fn () => new NormalizedProviderEntity('provider', EntityType::Work, 'id', $artist))
        ->toThrow(InvalidArgumentException::class);
});

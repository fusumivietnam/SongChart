<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
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

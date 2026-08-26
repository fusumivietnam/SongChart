<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\DTO\NormalizedAvailability;
use App\Domain\Providers\Normalization\DTO\NormalizedDestination;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\Validation\Enums\NormalizationFailureKind;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;

it('accepts a valid typed normalized artist', function (): void {
    $entity = new NormalizedProviderEntity(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'artist-mbid',
        data: normalizedArtistForValidation(),
        identifiers: [new NormalizedIdentifier('musicbrainz_artist', 'artist-mbid')],
    );

    expect((new DefaultNormalizedProviderEntityValidator)->validate($entity)->isValid())->toBeTrue();
});

it('classifies invalid identifier namespaces', function (): void {
    $entity = new NormalizedProviderEntity(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'artist-mbid',
        data: normalizedArtistForValidation(),
        identifiers: [new NormalizedIdentifier('Bad Namespace', 'artist-mbid')],
    );

    $result = (new DefaultNormalizedProviderEntityValidator)->validate($entity);

    expect($result->isValid())->toBeFalse()
        ->and($result->issues[0]->kind)->toBe(NormalizationFailureKind::InvalidIdentifier);
});

it('fails closed for unsafe destinations and invalid markets', function (): void {
    $entity = new NormalizedProviderEntity(
        providerSlug: 'spotify',
        entityType: EntityType::Artist,
        externalId: 'spotify-artist',
        data: normalizedArtistForValidation(),
        destinations: [new NormalizedDestination('artist', 'http://example.test/artist')],
        availability: [new NormalizedAvailability('vietnam', true)],
    );

    $result = (new DefaultNormalizedProviderEntityValidator)->validate($entity);

    expect($result->isValid())->toBeFalse()
        ->and($result->issues)->toHaveCount(2)
        ->and($result->issues[0]->kind)->toBe(NormalizationFailureKind::UnsafeUrl)
        ->and($result->issues[1]->kind)->toBe(NormalizationFailureKind::InvalidValue);
});

function normalizedArtistForValidation(): NormalizedArtist
{
    return new NormalizedArtist(
        name: ProviderField::provided('Radiohead'),
        sortName: ProviderField::missing(),
        disambiguation: ProviderField::missing(),
        countryCode: ProviderField::missing(),
        beginDate: ProviderField::missing(),
        endDate: ProviderField::missing(),
        ended: ProviderField::missing(),
    );
}

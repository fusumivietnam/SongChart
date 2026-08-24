<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\Validation\Enums\NormalizationFailureKind;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;

it('accepts a valid typed normalized artist', function (): void {
    $entity = new NormalizedProviderEntity(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'artist-mbid',
        data: new NormalizedArtist(
            name: ProviderField::provided('Radiohead'),
            sortName: ProviderField::missing(),
            disambiguation: ProviderField::missing(),
            countryCode: ProviderField::missing(),
            beginDate: ProviderField::missing(),
            endDate: ProviderField::missing(),
            ended: ProviderField::missing(),
        ),
        identifiers: [new NormalizedIdentifier('musicbrainz_artist', 'artist-mbid')],
    );

    expect((new DefaultNormalizedProviderEntityValidator)->validate($entity)->isValid())->toBeTrue();
});

it('classifies invalid identifier namespaces', function (): void {
    $entity = new NormalizedProviderEntity(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'artist-mbid',
        data: new NormalizedArtist(
            name: ProviderField::provided('Radiohead'),
            sortName: ProviderField::missing(),
            disambiguation: ProviderField::missing(),
            countryCode: ProviderField::missing(),
            beginDate: ProviderField::missing(),
            endDate: ProviderField::missing(),
            ended: ProviderField::missing(),
        ),
        identifiers: [new NormalizedIdentifier('Bad Namespace', 'artist-mbid')],
    );

    $result = (new DefaultNormalizedProviderEntityValidator)->validate($entity);

    expect($result->isValid())->toBeFalse()
        ->and($result->issues[0]->kind)->toBe(NormalizationFailureKind::InvalidIdentifier);
});

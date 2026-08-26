<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Support\Providers\Ingestion\ProviderImportPreviewBuilder;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;
use App\Support\Providers\Normalization\MusicBrainzProviderMapper;
use App\Support\Providers\Normalization\ProviderSpecificMapperRegistry;

it('builds a valid read-only preview from a supported provider payload', function (): void {
    $builder = new ProviderImportPreviewBuilder(
        new ProviderSpecificMapperRegistry([new MusicBrainzProviderMapper]),
        new DefaultNormalizedProviderEntityValidator,
    );

    $preview = $builder->build(new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Recording,
        externalId: 'recording-mbid',
        data: [
            'title' => 'Example Song',
            'length' => 181000,
            'isrcs' => ['USAAA2600001'],
        ],
        receivedAt: new DateTimeImmutable('2026-08-26T00:00:00+00:00'),
    ));

    expect($preview->valid)->toBeTrue()
        ->and($preview->counts['identifiers'])->toBe(2)
        ->and($preview->normalized['provider_slug'])->toBe('musicbrainz')
        ->and($preview->normalized['fields']['title'])->toBe(['presence' => 'provided', 'value' => 'Example Song']);
});

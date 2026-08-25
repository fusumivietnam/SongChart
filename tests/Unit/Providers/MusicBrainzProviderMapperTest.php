<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Support\Providers\Normalization\MusicBrainzProviderMapper;
use DateTimeImmutable;

it('maps MusicBrainz recording identity, ISRC and artist credits', function (): void {
    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Recording,
        externalId: 'recording-mbid',
        data: [
            'title' => 'Example Song',
            'length' => 181000,
            'isrcs' => ['USAAA2600001'],
            'artist-credit' => [
                ['artist' => ['id' => 'artist-mbid', 'name' => 'Example Artist']],
            ],
        ],
        receivedAt: new DateTimeImmutable('2026-08-26T00:00:00+00:00'),
    );

    $entity = (new MusicBrainzProviderMapper)->map($payload)->toArray();

    expect($entity['provider_slug'])->toBe('musicbrainz')
        ->and($entity['identifiers'][0])->toBe(['namespace' => 'musicbrainz_recording', 'value' => 'recording-mbid'])
        ->and($entity['identifiers'][1])->toBe(['namespace' => 'isrc', 'value' => 'USAAA2600001'])
        ->and($entity['relationships'][0]['type'])->toBe('performed-by')
        ->and($entity['relationships'][0]['target_external_id'])->toBe('artist-mbid');
});

it('maps MusicBrainz artist life span without inventing missing values', function (): void {
    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'artist-mbid',
        data: [
            'name' => 'Example Artist',
            'sort-name' => 'Artist, Example',
            'country' => 'GB',
            'life-span' => ['begin' => '2001', 'ended' => false],
        ],
        receivedAt: new DateTimeImmutable('2026-08-26T00:00:00+00:00'),
    );

    $fields = (new MusicBrainzProviderMapper)->map($payload)->toArray()['fields'];

    expect($fields['name'])->toBe(['presence' => 'provided', 'value' => 'Example Artist'])
        ->and($fields['beginDate'])->toBe(['presence' => 'provided', 'value' => '2001'])
        ->and($fields['endDate'])->toBe(['presence' => 'missing']);
});

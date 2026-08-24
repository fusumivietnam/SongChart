<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseGroup;
use App\Models\Provider;
use App\Support\Providers\Catalog\MusicBrainzProviderCatalogAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mutates MusicBrainz release groups and links a later release to its canonical group', function (): void {
    Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $pipeline = app(CanonicalMutationPipeline::class);

    $groupPayload = new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::ReleaseGroup,
        externalId: '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
        data: [
            'id' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
            'title' => 'Random Access Memories',
            'primary-type' => 'Album',
            'secondary-types' => [],
            'first-release-date' => '2013-05-17',
        ],
        receivedAt: new DateTimeImmutable('2026-08-17T00:00:00+00:00'),
    );
    $groupMutation = $pipeline->apply($adapter->normalize($groupPayload));
    $group = ReleaseGroup::query()->findOrFail($groupMutation->entityId);

    expect($group->slug)->toBe('random-access-memories')
        ->and($group->primary_type)->toBe('album')
        ->and($group->first_release_date?->format('Y-m-d'))->toBe('2013-05-17');

    $releasePayload = new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Release,
        externalId: 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
        data: [
            'id' => 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
            'title' => 'Random Access Memories',
            'status' => 'Official',
            'date' => '2013-05-17',
            'country' => 'XE',
            'barcode' => '0888837168618',
            'release-group' => [
                'id' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
                'primary-type' => 'Album',
            ],
            'media' => [['track-count' => 13]],
        ],
        receivedAt: new DateTimeImmutable('2026-08-17T00:00:01+00:00'),
    );
    $releaseMutation = $pipeline->apply($adapter->normalize($releasePayload));
    $release = Release::query()->findOrFail($releaseMutation->entityId);

    expect($release->release_group_id)->toBe($group->id)
        ->and($release->release_type)->toBe('album')
        ->and($release->released_on?->format('Y-m-d'))->toBe('2013-05-17')
        ->and($release->barcode)->toBe('0888837168618');

    $this->assertDatabaseHas('external_identifiers', [
        'entity_type' => 'release_group',
        'entity_id' => $group->id,
        'namespace' => 'musicbrainz_release_group',
        'value' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
    ]);
    $this->assertDatabaseHas('external_identifiers', [
        'entity_type' => 'release',
        'entity_id' => $release->id,
        'namespace' => 'musicbrainz_release',
        'value' => 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
    ]);
});

it('keeps partial MusicBrainz dates as assertions without forcing an invalid SQL date', function (): void {
    Provider::query()->create([
        'slug' => 'musicbrainz', 'name' => 'MusicBrainz', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz', entityType: EntityType::ReleaseGroup,
        externalId: '11111111-2222-3333-4444-555555555555',
        data: ['id' => '11111111-2222-3333-4444-555555555555', 'title' => 'Partial Date Album', 'primary-type' => 'Album', 'first-release-date' => '1999'],
        receivedAt: new DateTimeImmutable('2026-08-17T00:00:00+00:00'),
    );

    $mutation = app(CanonicalMutationPipeline::class)->apply($adapter->normalize($payload));
    $group = ReleaseGroup::query()->findOrFail($mutation->entityId);

    expect($group->first_release_date)->toBeNull();
    $this->assertDatabaseHas('metadata_assertions', [
        'entity_type' => 'release_group',
        'entity_id' => $group->id,
        'field_name' => 'firstReleaseDate',
    ]);
});

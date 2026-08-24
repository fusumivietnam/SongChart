<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Models\Catalog\Artist;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Work;
use App\Models\Provider;
use App\Support\Providers\Catalog\MusicBrainzProviderCatalogAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('materializes group members and persists canonical membership metadata', function (): void {
    Provider::query()->create([
        'slug' => 'musicbrainz', 'name' => 'MusicBrainz', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);

    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz', entityType: EntityType::Artist,
        externalId: '00000000-0000-0000-0000-000000000001',
        data: [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'BLACKPINK', 'type' => 'Group', 'country' => 'KR',
            'relations' => [[
                'target-type' => 'artist', 'type' => 'member of band', 'direction' => 'backward',
                'begin' => '2016-08-08', 'ended' => false,
                'artist' => [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'name' => 'JENNIE', 'sort-name' => 'JENNIE', 'type' => 'Person', 'country' => 'KR',
                ],
            ]],
        ],
        receivedAt: new DateTimeImmutable('2026-08-17T00:00:00+00:00'),
    );

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $result = app(CanonicalMutationPipeline::class)->apply($adapter->normalize($payload));
    $group = Artist::query()->findOrFail($result->entityId);
    $member = Artist::query()->where('name', 'JENNIE')->firstOrFail();

    expect($group->artist_type)->toBe('group')->and($member->artist_type)->toBe('person');
    $this->assertDatabaseHas('entity_relationships', [
        'subject_type' => 'artist', 'subject_id' => $group->id,
        'relationship_type' => 'has_member', 'object_type' => 'artist', 'object_id' => $member->id,
    ]);
});

it('persists artist credit join phrase and materializes a related work with ISWC', function (): void {
    Provider::query()->create([
        'slug' => 'musicbrainz', 'name' => 'MusicBrainz', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);
    $artist = Artist::query()->create([
        'name' => 'Artist A', 'slug' => 'artist-a', 'artist_type' => 'person', 'verification_state' => 'candidate',
    ]);
    ExternalIdentifier::query()->create([
        'entity_type' => EntityType::Artist, 'entity_id' => $artist->id,
        'namespace' => 'provider:musicbrainz', 'value' => '00000000-0000-0000-0000-000000000020',
        'is_primary' => true, 'verification_state' => 'candidate',
    ]);

    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz', entityType: EntityType::Recording,
        externalId: '00000000-0000-0000-0000-000000000021',
        data: [
            'id' => '00000000-0000-0000-0000-000000000021', 'title' => 'Collaboration',
            'artist-credit' => [[
                'name' => 'Artist A', 'joinphrase' => ' feat. ',
                'artist' => ['id' => '00000000-0000-0000-0000-000000000020', 'name' => 'Artist A'],
            ]],
            'relations' => [[
                'target-type' => 'work', 'type' => 'performance',
                'work' => [
                    'id' => '00000000-0000-0000-0000-000000000022', 'title' => 'Collaboration',
                    'type' => 'Song', 'languages' => ['eng'], 'iswcs' => ['T-123.456.789-0'],
                ],
            ]],
        ],
        receivedAt: new DateTimeImmutable('2026-08-17T00:00:00+00:00'),
    );

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $result = app(CanonicalMutationPipeline::class)->apply($adapter->normalize($payload));
    $recording = Recording::query()->findOrFail($result->entityId);
    $work = Work::query()->where('title', 'Collaboration')->firstOrFail();

    $this->assertDatabaseHas('artist_recording', [
        'artist_id' => $artist->id, 'recording_id' => $recording->id,
        'credited_name' => 'Artist A', 'join_phrase' => ' feat. ',
    ]);
    $this->assertDatabaseHas('entity_relationships', [
        'subject_type' => 'recording', 'subject_id' => $recording->id,
        'relationship_type' => 'recording_of', 'object_type' => 'work', 'object_id' => $work->id,
    ]);
    $this->assertDatabaseHas('external_identifiers', [
        'entity_type' => 'work', 'entity_id' => $work->id,
        'namespace' => 'iswc', 'value' => 'T-123.456.789-0',
    ]);
});

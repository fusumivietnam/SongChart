<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Models\Catalog\Artist;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Support\Providers\Catalog\MusicBrainzProviderCatalogAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mutates a MusicBrainz recording, attaches ISRC and links already-known artist credits', function (): void {
    Provider::query()->create([
        'slug' => 'musicbrainz', 'name' => 'MusicBrainz', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);

    $artist = Artist::query()->create([
        'name' => 'Daft Punk', 'sort_name' => 'Daft Punk', 'slug' => 'daft-punk',
        'artist_type' => 'group', 'verification_state' => 'candidate',
    ]);
    $this->assertNotNull($artist);
    ExternalIdentifier::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => $artist->id,
        'namespace' => 'provider:musicbrainz',
        'value' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56',
        'is_primary' => true,
        'verification_state' => 'candidate',
    ]);

    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz', entityType: EntityType::Recording,
        externalId: 'b91e5e9c-5f2a-4f40-8eb0-a5328cbbc327',
        data: [
            'id' => 'b91e5e9c-5f2a-4f40-8eb0-a5328cbbc327',
            'title' => 'Get Lucky',
            'length' => 369000,
            'isrcs' => ['USQX91300809'],
            'artist-credit' => [[
                'name' => 'Daft Punk',
                'artist' => ['id' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56', 'name' => 'Daft Punk'],
            ]],
        ],
        receivedAt: new DateTimeImmutable('2026-08-17T00:00:00+00:00'),
    );

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $mutation = app(CanonicalMutationPipeline::class)->apply($adapter->normalize($payload));
    $recording = Recording::query()->findOrFail($mutation->entityId);

    expect($recording->slug)->toBe('get-lucky')
        ->and($recording->duration_ms)->toBe(369000);

    $this->assertDatabaseHas('external_identifiers', [
        'entity_type' => 'recording', 'entity_id' => $recording->id,
        'namespace' => 'isrc', 'value' => 'USQX91300809',
    ]);
    $this->assertDatabaseHas('artist_recording', [
        'artist_id' => $artist->id, 'recording_id' => $recording->id,
        'credit_role' => 'primary', 'position' => 1,
    ]);
});

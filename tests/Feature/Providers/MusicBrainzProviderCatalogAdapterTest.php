<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use App\Support\Providers\Catalog\MusicBrainzProviderCatalogAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    Cache::clear();
    config()->set('songchart.providers.musicbrainz.enabled', true);
    config()->set('songchart.providers.musicbrainz.base_url', 'https://musicbrainz.test/ws/2');
    config()->set('songchart.providers.musicbrainz.user_agent', 'SongChartWeb/17.3.3 (dev@songchart.test)');
    config()->set('songchart.providers.musicbrainz.rate.strategy', 'minimum_interval');
    config()->set('songchart.providers.musicbrainz.rate.minimum_interval_ms', 1);
    config()->set('songchart.providers.musicbrainz.rate.default_cooldown_seconds', 2);
});

it('looks up and normalizes a MusicBrainz artist without leaking provider schema', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/artist/*' => Http::response([
            'id' => 'a74b1b7f-71a5-4011-9441-d0b5e4122711',
            'name' => 'Radiohead',
            'sort-name' => 'Radiohead',
            'country' => 'GB',
            'disambiguation' => '',
            'life-span' => ['begin' => '1985', 'ended' => false],
        ]),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $page = $adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE171',
        entityType: EntityType::Artist,
        externalId: 'a74b1b7f-71a5-4011-9441-d0b5e4122711',
    ));
    $normalized = $adapter->normalize($page->items[0]);

    expect($page->complete)->toBeTrue()
        ->and($normalized->normalizerVersion)->toBe('musicbrainz-artist-v2')
        ->and($normalized->identifiers[0]->namespace)->toBe('musicbrainz_artist')
        ->and($normalized->data->toArray()['name']['value'])->toBe('Radiohead')
        ->and($normalized->data->toArray()['countryCode']['value'])->toBe('GB');

    Http::assertSent(fn ($request): bool => $request->hasHeader('User-Agent', 'SongChartWeb/17.3.3 (dev@songchart.test)'));
});

it('refuses live traffic while MusicBrainz is not explicitly enabled', function (): void {
    config()->set('songchart.providers.musicbrainz.enabled', false);

    expect(fn () => (app(MusicBrainzProviderCatalogAdapter::class))->fetchPage(new ProviderImportContext(
        runId: '01STAGE171',
        entityType: EntityType::Artist,
        externalId: 'a74b1b7f-71a5-4011-9441-d0b5e4122711',
    )))->toThrow(RuntimeException::class, 'disabled');
});

it('classifies MusicBrainz temporary limits as retryable provider failures', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/artist/*' => Http::response([], 503, ['Retry-After' => '7']),
    ]);

    try {
        (app(MusicBrainzProviderCatalogAdapter::class))->fetchPage(new ProviderImportContext(
            runId: '01STAGE173',
            entityType: EntityType::Artist,
            externalId: 'a74b1b7f-71a5-4011-9441-d0b5e4122711',
        ));
        $this->fail('Expected retryable MusicBrainz provider request failure.');
    } catch (ProviderRequestException $exception) {
        expect($exception->retryable)->toBeTrue()
            ->and($exception->httpStatus)->toBe(503)
            ->and($exception->retryAfterSeconds)->toBe(7)
            ->and($exception->kind)->toBe('temporarily-unavailable');
    }
});

it('classifies a missing MusicBrainz entity as terminal', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/artist/*' => Http::response([], 404),
    ]);

    try {
        (app(MusicBrainzProviderCatalogAdapter::class))->fetchPage(new ProviderImportContext(
            runId: '01STAGE173',
            entityType: EntityType::Artist,
            externalId: '00000000-0000-0000-0000-000000000000',
        ));
        $this->fail('Expected terminal MusicBrainz provider request failure.');
    } catch (ProviderRequestException $exception) {
        expect($exception->retryable)->toBeFalse()
            ->and($exception->httpStatus)->toBe(404)
            ->and($exception->kind)->toBe('not-found');
    }
});

it('shares provider cooldown across subsequent MusicBrainz requests', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/artist/*' => Http::response([], 503, ['Retry-After' => '7']),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $context = new ProviderImportContext(
        runId: '01STAGE1733',
        entityType: EntityType::Artist,
        externalId: 'a74b1b7f-71a5-4011-9441-d0b5e4122711',
    );

    try {
        $adapter->fetchPage($context);
    } catch (ProviderRequestException) {
    }

    Http::fake(fn () => Http::response([
        'id' => 'a74b1b7f-71a5-4011-9441-d0b5e4122711',
        'name' => 'Radiohead',
    ]));

    try {
        $adapter->fetchPage($context);
        $this->fail('Expected provider cooldown to block the second request before HTTP dispatch.');
    } catch (ProviderRequestException $exception) {
        expect($exception->kind)->toBe('cooldown-active')
            ->and($exception->retryAfterSeconds)->toBeGreaterThanOrEqual(1);
    }

    Http::assertNothingSent();
});

it('looks up and normalizes a MusicBrainz release group as a distinct canonical entity type', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/release-group/*' => Http::response([
            'id' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
            'title' => 'Random Access Memories',
            'primary-type' => 'Album',
            'secondary-types' => [],
            'first-release-date' => '2013-05-17',
            'disambiguation' => '',
            'artist-credit' => [[
                'artist' => ['id' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56', 'name' => 'Daft Punk'],
            ]],
        ]),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $page = $adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE174RG',
        entityType: EntityType::ReleaseGroup,
        externalId: '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
    ));
    $normalized = $adapter->normalize($page->items[0]);

    expect($normalized->entityType)->toBe(EntityType::ReleaseGroup)
        ->and($normalized->normalizerVersion)->toBe('musicbrainz-release-group-v1')
        ->and($normalized->identifiers[0]->namespace)->toBe('musicbrainz_release_group')
        ->and($normalized->data->toArray()['title']['value'])->toBe('Random Access Memories')
        ->and($normalized->data->toArray()['primaryType']['value'])->toBe('Album')
        ->and($normalized->relationships)->toHaveCount(1)
        ->and($normalized->relationships[0]->targetEntityType)->toBe(EntityType::Artist);
});

it('looks up and normalizes a MusicBrainz release with edition metadata and release-group identity', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/release/*' => Http::response([
            'id' => 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
            'title' => 'Random Access Memories',
            'status' => 'Official',
            'date' => '2013-05-17',
            'country' => 'XE',
            'barcode' => '0888837168618',
            'packaging' => 'Jewel Case',
            'release-group' => [
                'id' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
                'title' => 'Random Access Memories',
                'primary-type' => 'Album',
            ],
            'label-info' => [['catalog-number' => '88883716861']],
            'media' => [['track-count' => 13, 'format' => 'CD']],
            'artist-credit' => [[
                'artist' => ['id' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56', 'name' => 'Daft Punk'],
            ]],
            'cover-art-archive' => ['front' => true, 'artwork' => true],
        ]),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $page = $adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE174REL',
        entityType: EntityType::Release,
        externalId: 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
    ));
    $normalized = $adapter->normalize($page->items[0]);
    $fields = $normalized->data->toArray();

    expect($normalized->entityType)->toBe(EntityType::Release)
        ->and($normalized->normalizerVersion)->toBe('musicbrainz-release-v1')
        ->and($normalized->identifiers[0]->namespace)->toBe('musicbrainz_release')
        ->and($fields['primaryType']['value'])->toBe('Album')
        ->and($fields['releaseGroupMbid']['value'])->toBe('4a2f1592-9e53-38ec-9e6c-1f6e6460e34a')
        ->and($fields['catalogNumber']['value'])->toBe('88883716861')
        ->and($fields['trackCount']['value'])->toBe(13)
        ->and($fields['cover_art_archive_front_url']['value'])->toBe('https://coverartarchive.org/release/b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a/front-500')
        ->and(collect($normalized->relationships)->contains(fn ($relationship): bool => $relationship->targetEntityType === EntityType::ReleaseGroup))->toBeTrue();
});

it('looks up and normalizes a MusicBrainz recording with artist credit and ISRC identities', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/recording/*' => Http::response([
            'id' => 'b91e5e9c-5f2a-4f40-8eb0-a5328cbbc327',
            'title' => 'Get Lucky',
            'length' => 369000,
            'disambiguation' => '',
            'isrcs' => ['USQX91300809'],
            'artist-credit' => [[
                'name' => 'Daft Punk',
                'artist' => ['id' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56', 'name' => 'Daft Punk'],
            ]],
            'releases' => [[
                'id' => 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
                'title' => 'Random Access Memories',
                'date' => '2013-05-17',
            ]],
        ]),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $page = $adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE175REC',
        entityType: EntityType::Recording,
        externalId: 'b91e5e9c-5f2a-4f40-8eb0-a5328cbbc327',
    ));
    $normalized = $adapter->normalize($page->items[0]);
    $fields = $normalized->data->toArray();

    expect($normalized->entityType)->toBe(EntityType::Recording)
        ->and($normalized->normalizerVersion)->toBe('musicbrainz-recording-v1')
        ->and($fields['title']['value'])->toBe('Get Lucky')
        ->and($fields['durationMs']['value'])->toBe(369000)
        ->and($fields['isrc']['value'])->toBe('USQX91300809')
        ->and(collect($normalized->identifiers)->contains(fn ($identifier): bool => $identifier->namespace === 'isrc' && $identifier->value === 'USQX91300809'))->toBeTrue()
        ->and(collect($normalized->relationships)->contains(fn ($relationship): bool => $relationship->targetEntityType === EntityType::Artist))->toBeTrue()
        ->and(collect($normalized->relationships)->contains(fn ($relationship): bool => $relationship->targetEntityType === EntityType::Release))->toBeTrue();
});

it('normalizes group membership, aliases and selected URLs from an artist lookup', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/artist/*' => Http::response([
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'BLACKPINK',
            'type' => 'Group',
            'country' => 'KR',
            'aliases' => [['name' => '블랙핑크']],
            'relations' => [[
                'target-type' => 'artist',
                'type' => 'member of band',
                'direction' => 'backward',
                'begin' => '2016-08-08',
                'ended' => false,
                'artist' => [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'name' => 'JENNIE',
                    'sort-name' => 'JENNIE',
                    'type' => 'Person',
                    'country' => 'KR',
                ],
            ], [
                'target-type' => 'url',
                'type' => 'official homepage',
                'url' => ['resource' => 'https://www.blackpinkofficial.com/'],
            ]],
        ]),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $page = $adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE176ART',
        entityType: EntityType::Artist,
        externalId: '00000000-0000-0000-0000-000000000001',
    ));
    $normalized = $adapter->normalize($page->items[0]);

    expect($normalized->data->toArray()['aliases']['value'])->toContain('블랙핑크')
        ->and(collect($normalized->relationships)->contains(fn ($relationship): bool => $relationship->type === 'has_member' && $relationship->targetExternalId === '00000000-0000-0000-0000-000000000002'))->toBeTrue()
        ->and(collect($normalized->identifiers)->contains(fn ($identifier): bool => str_starts_with($identifier->namespace, 'url:') && $identifier->value === 'https://www.blackpinkofficial.com/'))->toBeTrue();
});

it('normalizes recording to work relationships and supports work lookup', function (): void {
    Http::fake([
        'musicbrainz.test/ws/2/recording/*' => Http::response([
            'id' => '00000000-0000-0000-0000-000000000010',
            'title' => 'Pink Venom',
            'artist-credit' => [[
                'name' => 'BLACKPINK',
                'joinphrase' => '',
                'artist' => ['id' => '00000000-0000-0000-0000-000000000001', 'name' => 'BLACKPINK'],
            ]],
            'relations' => [[
                'target-type' => 'work',
                'type' => 'performance',
                'work' => [
                    'id' => '00000000-0000-0000-0000-000000000011',
                    'title' => 'Pink Venom',
                    'type' => 'Song',
                    'languages' => ['eng'],
                    'iswcs' => ['T-123.456.789-0'],
                ],
            ]],
        ]),
        'musicbrainz.test/ws/2/work/*' => Http::response([
            'id' => '00000000-0000-0000-0000-000000000011',
            'title' => 'Pink Venom',
            'type' => 'Song',
            'languages' => ['eng'],
            'iswcs' => ['T-123.456.789-0'],
        ]),
    ]);

    $adapter = app(MusicBrainzProviderCatalogAdapter::class);
    $recording = $adapter->normalize($adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE176REC', entityType: EntityType::Recording,
        externalId: '00000000-0000-0000-0000-000000000010',
    ))->items[0]);

    expect(collect($recording->relationships)->contains(fn ($relationship): bool => $relationship->type === 'recording_of' && $relationship->targetEntityType === EntityType::Work))->toBeTrue();

    Cache::clear();
    $work = $adapter->normalize($adapter->fetchPage(new ProviderImportContext(
        runId: '01STAGE176WORK', entityType: EntityType::Work,
        externalId: '00000000-0000-0000-0000-000000000011',
    ))->items[0]);

    expect($work->entityType)->toBe(EntityType::Work)
        ->and(collect($work->identifiers)->contains(fn ($identifier): bool => $identifier->namespace === 'iswc'))->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderStatus;
use App\Jobs\Providers\Ingestion\FetchProviderImportPage;
use App\Models\Catalog\Artist;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('songchart.providers.musicbrainz.enabled', true);
    config()->set('songchart.providers.musicbrainz.user_agent', 'SongChartWeb/0.1 (dev@example.test)');
    Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
});

it('searches MusicBrainz artists from the development control center', function (): void {
    Http::fake([
        'https://musicbrainz.org/ws/2/artist*' => Http::response([
            'count' => 1,
            'artists' => [[
                'id' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56',
                'name' => 'Daft Punk',
                'sort-name' => 'Daft Punk',
                'country' => 'FR',
                'type' => 'Group',
                'disambiguation' => 'French electronic music duo',
            ]],
        ]),
    ]);

    $this->get('/development/status?musicbrainz_query=Daft%20Punk')
        ->assertOk()
        ->assertSee('MusicBrainz artist search and import')
        ->assertSee('Daft Punk')
        ->assertSee('056e4f3e-d505-4dad-8ec1-d04f521cbb56');
});

it('queues a selected MusicBrainz artist through the provider import orchestrator', function (): void {
    Queue::fake();

    $this->post('/development/providers/musicbrainz/artists/import', [
        'mbid' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56',
    ])->assertRedirect('/development/status');

    Queue::assertPushed(FetchProviderImportPage::class);
    $this->assertDatabaseHas('provider_import_runs', [
        'operation' => 'artist-lookup',
        'status' => 'queued',
    ]);
});

it('exposes a canonical artist URL alias for imported catalog artists', function (): void {
    $artist = Artist::factory()->create(['slug' => 'daft-punk', 'name' => 'Daft Punk']);

    $this->get('/artists/'.$artist->slug)
        ->assertOk()
        ->assertSee('Daft Punk');
});

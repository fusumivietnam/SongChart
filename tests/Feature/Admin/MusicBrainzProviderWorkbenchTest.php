<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderStatus;
use App\Enums\UserRole;
use App\Jobs\Providers\Ingestion\FetchProviderImportPage;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function stage1732ProviderAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-17-3-2'),
        'two_factor_confirmed_at' => now(),
    ]);
}

beforeEach(function (): void {
    config()->set('songchart.providers.musicbrainz.enabled', true);
    config()->set('songchart.providers.musicbrainz.user_agent', 'SongChartWeb/17.3.3 (dev@example.test)');
    config()->set('songchart.providers.musicbrainz.rate.strategy', 'minimum_interval');
    config()->set('songchart.providers.musicbrainz.rate.minimum_interval_ms', 1);
});

it('searches MusicBrainz from the provider admin workbench', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

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

    $this->actingAs(stage1732ProviderAdmin())
        ->get(route('admin.providers.show', ['provider' => $provider->id, 'musicbrainz_query' => 'Daft Punk']))
        ->assertOk()
        ->assertSee('Provider import workbench')
        ->assertSee('minimum_interval')
        ->assertSee('available')
        ->assertSee('Daft Punk')
        ->assertSee('056e4f3e-d505-4dad-8ec1-d04f521cbb56');
});

it('queues a selected MusicBrainz artist from admin through the governed import route', function (): void {
    Queue::fake();
    $provider = Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

    $this->actingAs(stage1732ProviderAdmin())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.musicbrainz.artists.import', $provider), [
            'mbid' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56',
        ])
        ->assertRedirect(route('admin.providers.show', $provider));

    Queue::assertPushed(FetchProviderImportPage::class);
    $this->assertDatabaseHas('provider_import_runs', [
        'provider_id' => $provider->id,
        'operation' => 'artist-lookup',
        'status' => 'queued',
    ]);
});

it('searches MusicBrainz release groups and releases from the provider admin workbench', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

    Http::fake([
        'https://musicbrainz.org/ws/2/release-group*' => Http::response([
            'count' => 1,
            'release-groups' => [[
                'id' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
                'title' => 'Random Access Memories',
                'primary-type' => 'Album',
                'first-release-date' => '2013-05-17',
            ]],
        ]),
        'https://musicbrainz.org/ws/2/release*' => Http::response([
            'count' => 1,
            'releases' => [[
                'id' => 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
                'title' => 'Random Access Memories',
                'status' => 'Official',
                'date' => '2013-05-17',
                'country' => 'XE',
                'release-group' => ['id' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a', 'title' => 'Random Access Memories'],
            ]],
        ]),
    ]);

    $admin = stage1732ProviderAdmin();

    $this->actingAs($admin)
        ->get(route('admin.providers.show', ['provider' => $provider->id, 'musicbrainz_release_group_query' => 'Random Access Memories']))
        ->assertOk()
        ->assertSee('Release Group search')
        ->assertSee('4a2f1592-9e53-38ec-9e6c-1f6e6460e34a');

    $this->actingAs($admin)
        ->get(route('admin.providers.show', ['provider' => $provider->id, 'musicbrainz_release_query' => 'Random Access Memories']))
        ->assertOk()
        ->assertSee('Release search')
        ->assertSee('b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a');
});

it('queues MusicBrainz release-group and release imports through the governed admin route', function (): void {
    Queue::fake();
    $provider = Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
    $admin = stage1732ProviderAdmin();

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.musicbrainz.releases.import', $provider), [
            'entity_type' => 'release_group',
            'mbid' => '4a2f1592-9e53-38ec-9e6c-1f6e6460e34a',
        ])
        ->assertRedirect(route('admin.providers.show', $provider));

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.musicbrainz.releases.import', $provider), [
            'entity_type' => 'release',
            'mbid' => 'b1a9c0e9-d987-4f37-b98a-3f7d9d3e1d8a',
        ])
        ->assertRedirect(route('admin.providers.show', $provider));

    Queue::assertPushed(FetchProviderImportPage::class, 2);
    $this->assertDatabaseHas('provider_import_runs', ['provider_id' => $provider->id, 'operation' => 'release-group-lookup']);
    $this->assertDatabaseHas('provider_import_runs', ['provider_id' => $provider->id, 'operation' => 'release-lookup']);
});

it('searches and queues MusicBrainz recordings from the provider admin workbench', function (): void {
    Queue::fake();
    $provider = Provider::query()->create([
        'slug' => 'musicbrainz', 'name' => 'MusicBrainz', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);

    Http::fake([
        'https://musicbrainz.org/ws/2/recording*' => Http::response([
            'count' => 1,
            'recordings' => [[
                'id' => 'b91e5e9c-5f2a-4f40-8eb0-a5328cbbc327',
                'title' => 'Get Lucky',
                'length' => 369000,
                'isrcs' => ['USQX91300809'],
                'artist-credit' => [[
                    'name' => 'Daft Punk',
                    'artist' => ['id' => '056e4f3e-d505-4dad-8ec1-d04f521cbb56', 'name' => 'Daft Punk'],
                ]],
            ]],
        ]),
    ]);

    $admin = stage1732ProviderAdmin();
    $this->actingAs($admin)
        ->get(route('admin.providers.show', ['provider' => $provider->id, 'musicbrainz_recording_query' => 'Get Lucky']))
        ->assertOk()
        ->assertSee('Recording search')
        ->assertSee('Get Lucky')
        ->assertSee('USQX91300809');

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.musicbrainz.recordings.import', $provider), [
            'mbid' => 'b91e5e9c-5f2a-4f40-8eb0-a5328cbbc327',
        ])
        ->assertRedirect(route('admin.providers.show', $provider));

    Queue::assertPushed(FetchProviderImportPage::class);
    $this->assertDatabaseHas('provider_import_runs', [
        'provider_id' => $provider->id, 'operation' => 'recording-lookup', 'status' => 'queued',
    ]);
});

<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use App\Models\Catalog\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders provider-neutral canonical social and structured metadata for an artist', function (): void {
    Artist::factory()->create([
        'name' => 'Thomas Bangalter',
        'slug' => 'thomas-bangalter',
        'artist_type' => 'person',
        'country_code' => 'FR',
    ]);

    $response = $this->get('/artists/thomas-bangalter');

    $response->assertOk()
        ->assertSee('<title>Thomas Bangalter', false)
        ->assertSee('<link rel="canonical" href="http://localhost/artists/thomas-bangalter">', false)
        ->assertSee('<meta property="og:url" content="http://localhost/artists/thomas-bangalter">', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"Person"', false)
        ->assertDontSee('musicbrainz.org', false);
});

it('distinguishes group artists in structured metadata', function (): void {
    Artist::factory()->create([
        'name' => 'Daft Punk',
        'slug' => 'daft-punk',
        'artist_type' => 'group',
        'country_code' => 'FR',
    ]);

    $this->get('/groups/daft-punk')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/groups/daft-punk">', false)
        ->assertSee('"@type":"MusicGroup"', false)
        ->assertDontSee('"@type":"Person"', false);
});

it('renders music album schema on canonical release pages', function (): void {
    Release::factory()->create([
        'title' => 'Random Access Memories',
        'slug' => 'random-access-memories',
    ]);

    $this->get('/releases/random-access-memories')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/releases/random-access-memories">', false)
        ->assertSee('"@type":"MusicAlbum"', false);
});

it('keeps search and filtered catalog pages out of the index while preserving canonical roots', function (): void {
    Artist::factory()->create([
        'name' => 'Thomas Bangalter',
        'slug' => 'thomas-bangalter',
        'artist_type' => 'person',
    ]);

    $this->get('/search?q=Thomas')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/search">', false)
        ->assertSee('<meta name="robots" content="noindex,follow">', false);

    $this->get('/artists?q=Thomas')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/artists">', false)
        ->assertSee('<meta name="robots" content="noindex,follow">', false);

    $this->get('/artists')
        ->assertOk()
        ->assertSee('content="index,follow,max-image-preview:large"', false);
});

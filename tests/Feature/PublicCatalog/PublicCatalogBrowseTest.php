<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('separates solo artists and groups on the public taxonomy', function (): void {
    Artist::factory()->create(['name' => 'Daft Punk', 'slug' => 'daft-punk', 'country_code' => 'FR', 'artist_type' => 'group']);
    Artist::factory()->create(['name' => 'Thomas Bangalter', 'slug' => 'thomas-bangalter', 'country_code' => 'FR', 'artist_type' => 'person']);

    $this->get('/groups')
        ->assertOk()
        ->assertSee('Daft Punk')
        ->assertSee('/groups/daft-punk', false)
        ->assertDontSee('Thomas Bangalter');

    $this->get('/artists')
        ->assertOk()
        ->assertSee('Thomas Bangalter')
        ->assertSee('/artists/thomas-bangalter', false)
        ->assertDontSee('Daft Punk');

    $this->get('/groups/daft-punk')->assertOk();
    $this->get('/artists/daft-punk')->assertNotFound();
});

it('keeps releases routable and links imported releases to the public shortcut', function (): void {
    $this->get('/releases')
        ->assertOk()
        ->assertSee('Chưa có Release canonical phù hợp.');

    Release::factory()->create(['title' => 'Random Access Memories', 'slug' => 'random-access-memories']);

    $this->get('/releases')
        ->assertOk()
        ->assertSee('Random Access Memories')
        ->assertSee('/releases/random-access-memories', false);

    $this->get('/releases/random-access-memories')->assertOk();
});

it('lists only public collections on the public collections path', function (): void {
    Collection::factory()->create(['title' => 'Private Picks', 'slug' => 'private-picks', 'visibility' => 'private']);
    Collection::factory()->create(['title' => 'Editor Picks', 'slug' => 'editor-picks', 'visibility' => 'public']);

    $this->get('/collections')
        ->assertOk()
        ->assertSee('Editor Picks')
        ->assertDontSee('Private Picks');
});

it('lists imported recordings and exposes the canonical recording shortcut', function (): void {
    Recording::factory()->create(['title' => 'Get Lucky', 'slug' => 'get-lucky', 'duration_ms' => 369000]);

    $this->get('/recordings')
        ->assertOk()
        ->assertSee('data-public-catalog-index="recording"', false)
        ->assertSee('Get Lucky')
        ->assertSee('/recordings/get-lucky', false);

    $this->get('/recordings/get-lucky')->assertOk();
});

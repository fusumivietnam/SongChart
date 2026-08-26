<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('publishes only canonical public surfaces in the sitemap', function (): void {
    Artist::factory()->create(['name' => 'Daft Punk', 'slug' => 'daft-punk', 'artist_type' => 'group']);
    Release::factory()->create(['title' => 'Discovery', 'slug' => 'discovery']);
    Recording::factory()->create(['title' => 'One More Time', 'slug' => 'one-more-time']);
    Collection::factory()->create(['title' => 'Public Picks', 'slug' => 'public-picks', 'visibility' => 'public']);
    Collection::factory()->create(['title' => 'Private Picks', 'slug' => 'private-picks', 'visibility' => 'private']);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
        ->assertSee('/groups/daft-punk', false)
        ->assertSee('/releases/discovery', false)
        ->assertSee('/recordings/one-more-time', false)
        ->assertSee('/collections/public-picks', false)
        ->assertDontSee('/collections/private-picks', false)
        ->assertDontSee('/admin', false)
        ->assertDontSee('/search?', false);
});

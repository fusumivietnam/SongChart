<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use App\Models\Catalog\Release;
use App\Support\Search\EloquentSearchCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('ranks exact prefix and substring canonical titles deterministically', function (): void {
    Artist::factory()->create(['name' => 'The Muse Archive', 'slug' => 'the-muse-archive', 'artist_type' => 'person']);
    Artist::factory()->create(['name' => 'Muse Live', 'slug' => 'muse-live', 'artist_type' => 'person']);
    Artist::factory()->create(['name' => 'Muse', 'slug' => 'muse', 'artist_type' => 'person']);

    $result = app(EloquentSearchCatalog::class)->search('mUsE');

    expect(array_column($result['items'], 'title'))->toBe([
        'Muse',
        'Muse Live',
        'The Muse Archive',
    ])->and(array_column($result['items'], 'search_rank'))->toBe([0, 1, 2]);
});

it('keeps facet counts query wide while a selected type scopes displayed results', function (): void {
    Artist::factory()->create(['name' => 'Radiohead', 'slug' => 'radiohead', 'artist_type' => 'group']);
    Release::factory()->create(['title' => 'Radiohead Live', 'slug' => 'radiohead-live']);

    $result = app(EloquentSearchCatalog::class)->search('Radiohead', 'artist');

    expect($result['total'])->toBe(1)
        ->and($result['total_all'])->toBe(2)
        ->and($result['counts']['artist'])->toBe(1)
        ->and($result['counts']['release'])->toBe(1)
        ->and($result['items'][0]['title'])->toBe('Radiohead')
        ->and($result['items'][0]['url'])->toBe(route('groups.show', ['slug' => 'radiohead']));
});

it('uses canonical identity as the final stable relevance tie break', function (): void {
    $first = Artist::factory()->create(['name' => 'Echo', 'slug' => 'echo-a', 'artist_type' => 'person']);
    $second = Artist::factory()->create(['name' => 'Echo', 'slug' => 'echo-b', 'artist_type' => 'person']);

    $result = app(EloquentSearchCatalog::class)->search('Echo');
    $expected = collect([$first, $second])->sortBy(fn (Artist $artist): string => (string) $artist->getKey())->pluck('slug')->values()->all();

    expect(array_column($result['items'], 'slug'))->toBe($expected);
});

<?php

declare(strict_types=1);

use App\Contracts\Search\SearchCatalog;
use App\Models\Catalog\Recording;
use App\Models\Catalog\RecordingVersion;
use App\Support\Search\EloquentSearchCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('searches recording versions by their native name column', function (): void {
    $this->app->forgetInstance(SearchCatalog::class);
    $this->app->singleton(SearchCatalog::class, EloquentSearchCatalog::class);

    $suffix = strtolower((string) Str::ulid());
    $recordingSlug = 'creep-search-version-parent-'.$suffix;
    $versionSlug = 'creep-acoustic-session-search-'.$suffix;

    $recording = Recording::factory()->create([
        'title' => 'Creep',
        'slug' => $recordingSlug,
    ]);

    RecordingVersion::factory()->create([
        'recording_id' => $recording->getKey(),
        'name' => 'Acoustic Session',
        'slug' => $versionSlug,
    ]);

    $this->get('/search?q=Acoustic%20Session&type=version')
        ->assertOk()
        ->assertSee('Acoustic Session')
        ->assertSee('href="'.route('versions.show', ['slug' => $versionSlug]).'"', false);
});

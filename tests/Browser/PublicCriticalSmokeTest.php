<?php

declare(strict_types=1);

use App\Models\Catalog\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stage23PublicDiscoveryFixture(): void
{
    Artist::factory()->create([
        'name' => 'Radiohead',
        'sort_name' => 'Radiohead',
        'slug' => 'radiohead',
        'artist_type' => 'group',
        'verification_state' => 'verified',
    ]);
}

it('captures Stage 23 discovery and search visual evidence on desktop', function (): void {
    stage23PublicDiscoveryFixture();

    $home = visit('/')
        ->assertSee('Bạn muốn tìm gì hôm nay?')
        ->assertSee('Radiohead')
        ->assertNoSmoke();
    $home->screenshot(filename: 'stage23-home-desktop', fullPage: true);

    $searchEmpty = visit('/search')
        ->assertSee('Tìm đúng thực thể âm nhạc')
        ->assertSee('Bắt đầu bằng một từ khóa')
        ->assertNoSmoke();
    $searchEmpty->screenshot(filename: 'stage23-search-empty-desktop', fullPage: true);

    $searchResults = visit('/search?q=Radiohead')
        ->assertSee('Kết quả cho “Radiohead”')
        ->assertSee('Radiohead')
        ->assertSee('Đã xác minh')
        ->assertNoSmoke();
    $searchResults->screenshot(filename: 'stage23-search-results-desktop', fullPage: true);

    $searchMissing = visit('/search?q=DefinitelyMissing')
        ->assertSee('Không tìm thấy “DefinitelyMissing”')
        ->assertNoSmoke();
    $searchMissing->screenshot(filename: 'stage23-search-missing-desktop', fullPage: true);
});

it('captures Stage 23 discovery and search visual evidence on mobile', function (): void {
    stage23PublicDiscoveryFixture();

    $home = visit('/')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Bạn muốn tìm gì hôm nay?')
        ->assertSee('Radiohead')
        ->assertNoSmoke();
    $home->screenshot(filename: 'stage23-home-mobile', fullPage: true);

    $searchEmpty = visit('/search')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Tìm đúng thực thể âm nhạc')
        ->assertSee('Bắt đầu bằng một từ khóa')
        ->assertNoSmoke();
    $searchEmpty->screenshot(filename: 'stage23-search-empty-mobile', fullPage: true);

    $searchResults = visit('/search?q=Radiohead')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Kết quả cho “Radiohead”')
        ->assertSee('Radiohead')
        ->assertSee('Đã xác minh')
        ->assertNoSmoke();
    $searchResults->screenshot(filename: 'stage23-search-results-mobile', fullPage: true);

    $searchMissing = visit('/search?q=DefinitelyMissing')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Không tìm thấy “DefinitelyMissing”')
        ->assertNoSmoke();
    $searchMissing->screenshot(filename: 'stage23-search-missing-mobile', fullPage: true);
});

it('captures representative Stage 23 canonical entity detail evidence on desktop', function (): void {
    $group = visit('/groups/nirvana')
        ->assertSee('Nirvana')
        ->assertSee('Độ tin cậy dữ liệu')
        ->assertSee('Chọn nơi nghe')
        ->assertNoSmoke();
    $group->screenshot(filename: 'stage23-entity-group-desktop', fullPage: true);

    $recording = visit('/recordings/paranoid-android')
        ->assertSee('Paranoid Android')
        ->assertSee('Tổng quan')
        ->assertSee('Nguồn và provenance')
        ->assertNoSmoke();
    $recording->screenshot(filename: 'stage23-entity-recording-desktop', fullPage: true);
});

it('captures representative Stage 23 canonical entity detail evidence on mobile', function (): void {
    $group = visit('/groups/nirvana')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Nirvana')
        ->assertSee('Độ tin cậy dữ liệu')
        ->assertSee('Chọn nơi nghe')
        ->assertNoSmoke();
    $group->screenshot(filename: 'stage23-entity-group-mobile', fullPage: true);

    $recording = visit('/recordings/paranoid-android')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Paranoid Android')
        ->assertSee('Tổng quan')
        ->assertSee('Nguồn và provenance')
        ->assertNoSmoke();
    $recording->screenshot(filename: 'stage23-entity-recording-mobile', fullPage: true);
});

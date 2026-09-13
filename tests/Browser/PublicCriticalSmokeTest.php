<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\Catalog\Artist;
use App\Models\Catalog\Recording;
use App\Support\Chart\DatabaseChartSnapshotStore;
use App\Support\Chart\YouTubeViewCountObservationSource;
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

function stage23PublicChartFixture(): void
{
    $recording = Recording::factory()->create([
        'title' => 'Chart Evidence Song',
        'slug' => 'chart-evidence-song',
    ]);

    $snapshot = (new BuildChartSnapshot)->handle(
        'youtube-video-views',
        YouTubeViewCountObservationSource::METRIC,
        new DateTimeImmutable,
        [
            new ChartMetricObservation(
                'stage23-browser-chart-observation',
                (string) $recording->getKey(),
                'youtube',
                'stage23-browser-video',
                YouTubeViewCountObservationSource::METRIC,
                123456,
                new DateTimeImmutable('-5 minutes'),
                YouTubeViewCountObservationSource::METRIC_UNIT,
                YouTubeViewCountObservationSource::SEMANTICS_VERSION,
                new DateTimeImmutable('-4 minutes'),
                'youtube:videos.list:stage23-browser-video:statistics',
            ),
        ],
    );

    app(DatabaseChartSnapshotStore::class)->append($snapshot);
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

it('captures Stage 23 chart unavailable and persisted provenance evidence on desktop', function (): void {
    $unavailable = visit('/charts/youtube-video-views')
        ->assertSee('Chưa có đủ bằng chứng')
        ->assertSee('Chưa thể công bố thứ hạng')
        ->assertNoSmoke();
    $unavailable->screenshot(filename: 'stage23-chart-unavailable-desktop', fullPage: true);

    stage23PublicChartFixture();

    $chart = visit('/charts/youtube-video-views')
        ->assertSee('Chart Evidence Song')
        ->assertSee('123.456')
        ->assertSee('Nguồn và provenance')
        ->assertNoSmoke();
    $chart->screenshot(filename: 'stage23-chart-observed-desktop', fullPage: true);
});

it('captures Stage 23 chart unavailable and persisted provenance evidence on mobile', function (): void {
    $unavailable = visit('/charts/youtube-video-views')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Chưa có đủ bằng chứng')
        ->assertSee('Chưa thể công bố thứ hạng')
        ->assertNoSmoke();
    $unavailable->screenshot(filename: 'stage23-chart-unavailable-mobile', fullPage: true);

    stage23PublicChartFixture();

    $chart = visit('/charts/youtube-video-views')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Chart Evidence Song')
        ->assertSee('123.456')
        ->assertSee('Nguồn và provenance')
        ->assertNoSmoke();
    $chart->screenshot(filename: 'stage23-chart-observed-mobile', fullPage: true);
});

it('proves Stage 23 mobile shell accessibility and fixed-navigation clearance', function (): void {
    stage23PublicDiscoveryFixture();

    visit('/search?q=Radiohead')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Kết quả cho “Radiohead”')
        ->assertNoAccessibilityIssues()
        ->assertScript(
            "(() => { const nav = document.querySelector('.sc-mobile-nav'); if (!nav) return false; const reserve = parseFloat(getComputedStyle(document.body).paddingBottom); return reserve >= nav.getBoundingClientRect().height; })()",
            true,
        )
        ->assertScript(
            "(() => [...document.querySelectorAll('.sc-mobile-nav-item')].every((item) => { const rect = item.getBoundingClientRect(); return rect.width >= 44 && rect.height >= 44; }))()",
            true,
        )
        ->assertScript(
            "document.querySelector('main#main-content') !== null && document.querySelector('nav[aria-label=\"Điều hướng di động\"]') !== null",
            true,
        )
        ->assertScript(
            "(() => { const input = document.querySelector('input[name=\"q\"]'); if (!input) return false; input.focus(); const style = getComputedStyle(input); const outlined = parseFloat(style.outlineWidth) >= 2 && style.outlineStyle !== 'none'; const ringed = style.boxShadow !== 'none' && style.boxShadow !== ''; return input.matches(':focus-visible') && (outlined || ringed); })()",
            true,
        )
        ->assertScript(
            "(() => { const radio = document.querySelector('input[type=\"radio\"][name=\"type\"]'); const chip = radio?.nextElementSibling; if (!radio || !chip) return false; radio.focus(); const style = getComputedStyle(chip); return radio.matches(':focus-visible') && parseFloat(style.outlineWidth) >= 3 && style.outlineStyle !== 'none'; })()",
            true,
        )
        ->assertScript(
            "(() => { const containsReducedMotion = (rules) => [...rules].some((rule) => (rule instanceof CSSMediaRule && rule.conditionText.includes('prefers-reduced-motion') && rule.conditionText.includes('reduce')) || ('cssRules' in rule && containsReducedMotion(rule.cssRules))); return [...document.styleSheets].some((sheet) => { try { return containsReducedMotion(sheet.cssRules); } catch { return false; } }); })()",
            true,
        )
        ->assertScript("document.querySelector('[wire\\\\:id]') === null", true)
        ->assertNoSmoke();

    visit('/recordings/paranoid-android')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Paranoid Android')
        ->assertNoAccessibilityIssues()
        ->assertScript("document.querySelector('[wire\\\\:id]') === null", true)
        ->assertNoSmoke();

    stage23PublicChartFixture();

    visit('/charts/youtube-video-views')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Chart Evidence Song')
        ->assertNoAccessibilityIssues()
        ->assertScript("document.querySelector('[wire\\\\:id]') === null", true)
        ->assertNoSmoke();
});

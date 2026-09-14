<?php

declare(strict_types=1);

it('renders entity facets with counts for a mixed query', function (): void {
    $this->get('/search?q=Radiohead')
        ->assertOk()
        ->assertSee('Loại thực thể')
        ->assertSee('Hiển thị 1–5 trong')
        ->assertSee('aria-label="Lọc kết quả theo loại thực thể"', false)
        ->assertSee('aria-label="Danh sách kết quả"', false);
});

it('uses one document main landmark and labels the search result region', function (): void {
    $response = $this->get('/search?q=Radiohead');

    $response->assertOk()
        ->assertSee('<main id="main-content"', false)
        ->assertSee('id="search-results"', false)
        ->assertSee('aria-labelledby="search-results-title"', false)
        ->assertSee('id="search-results-title"', false);

    expect(substr_count($response->getContent(), '<main'))->toBe(1);
});

it('preserves query and sorting when switching entity facets', function (): void {
    $this->get('/search?q=Radiohead&sort=year_desc')
        ->assertOk()
        ->assertSee('q=Radiohead&amp;type=release&amp;sort=year_desc', false);
});

it('paginates deterministic canonical results', function (): void {
    $this->get('/search?q=Radiohead')
        ->assertOk()
        ->assertSee('data-current-page="1"', false)
        ->assertSee('data-last-page="3"', false)
        ->assertSee('page=2', false);

    $this->get('/search?q=Radiohead&page=3')
        ->assertOk()
        ->assertSee('data-current-page="3"', false)
        ->assertSee('data-last-page="3"', false)
        ->assertSee('True Love Waits — Live')
        ->assertSee('Chưa xác minh');
});

it('renders a scoped empty state when a facet has no matches', function (): void {
    $this->get('/search?q=Radiohead&type=collection')
        ->assertOk()
        ->assertSee('Không có kết quả trong bộ lọc này')
        ->assertSee('Xem tất cả');
});

it('uses unique IDs for header and page search inputs', function (): void {
    $response = $this->get('/search?q=Radiohead');

    $response->assertOk()
        ->assertSee('id="header-search"', false)
        ->assertSee('id="catalog-search"', false);

    expect(substr_count($response->getContent(), 'id="header-search"'))->toBe(1)
        ->and(substr_count($response->getContent(), 'id="catalog-search"'))->toBe(1);
});

it('rejects invalid and out of range pages', function (): void {
    $this->get('/search?q=Radiohead&page=0')->assertSessionHasErrors('page');
    $this->get('/search?q=Radiohead&page=99')->assertNotFound();
});

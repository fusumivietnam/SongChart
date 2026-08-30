<?php

declare(strict_types=1);

it('renders the governed artist detail composition on the canonical plural URL', function (): void {
    $this->get('/groups/radiohead')->assertOk()
        ->assertSee('data-entity-type="artist"', false)
        ->assertSee('Canonical artist identity')
        ->assertSee('MusicBrainz Artist ID')
        ->assertSee('/releases/ok-computer', false)
        ->assertSee('Data passport')
        ->assertSee('Identity bridge')
        ->assertSee('1 connected identity sources.')
        ->assertSee('Enrichment plan')
        ->assertSee('Nguồn và provenance');
});

it('keeps the public entity page to one main landmark with a labelled entity article', function (): void {
    $response = $this->get('/groups/radiohead');

    $response->assertOk()
        ->assertSee('<article class="min-w-0 space-y-10" aria-labelledby="entity-title">', false)
        ->assertSee('id="entity-title"', false);

    expect(substr_count($response->getContent(), '<main'))->toBe(1);
});

it('keeps provenance data usable on narrow viewports', function (): void {
    $this->get('/groups/radiohead')->assertOk()
        ->assertSee('overflow-x-auto', false)
        ->assertSee('tabindex="0"', false)
        ->assertSee('aria-label="Bảng nguồn và provenance có thể cuộn ngang"', false)
        ->assertSee('min-w-[36rem]', false);
});

it('renders entity-specific relationships and identifiers on canonical plural URLs', function (string $path, string $expected): void {
    $this->get($path)->assertOk()->assertSee($expected);
})->with([
    ['/releases/ok-computer', 'Nghệ sĩ và danh sách bản thu'],
    ['/recordings/paranoid-android', 'ISRC'],
    ['/works/creep', 'Bản thu và phiên bản của tác phẩm'],
    ['/versions/creep-acoustic', 'Tác phẩm, bản thu gốc và nghệ sĩ'],
    ['/collections/alternative-essentials', 'Các mục trong bộ sưu tập'],
]);

it('does not expose the legacy generic or singular entity detail surfaces', function (string $path): void {
    $this->get($path)->assertNotFound();
})->with([
    '/entity/artist/radiohead',
    '/artist/radiohead',
    '/release/ok-computer',
    '/recording/paranoid-android',
    '/work/creep',
]);

it('keeps unknown identifiers explicit instead of fabricating them', function (): void {
    $this->get('/works/creep')->assertOk()->assertSee('Chưa có định danh bên ngoài đã được xác minh.');
});

it('keeps provider availability explicit', function (): void {
    $this->get('/releases/ok-computer')->assertOk()->assertSee('Có sẵn')->assertSee('Chưa xác định')->assertSee('Cần kiểm tra lại');
});

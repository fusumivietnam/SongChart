<?php

declare(strict_types=1);

it('renders the search-first homepage', function (): void {
    $this->get('/')->assertOk()->assertSee('Bạn muốn tìm gì hôm nay?')->assertSee('Tìm kiếm');
});

it('returns matching canonical entities', function (): void {
    $this->get('/search?q=Radiohead')
        ->assertOk()
        ->assertSee('Kết quả cho “Radiohead”')
        ->assertSee('Radiohead')
        ->assertSee('OK Computer')
        ->assertSee('Không có lượt nghe, chart hoặc độ phổ biến giả lập.');
});

it('filters search results by entity type', function (): void {
    $this->get('/search?q=Radiohead&type=artist')
        ->assertOk()
        ->assertSee('1 kết quả')
        ->assertSee('href="'.route('artists.show', ['slug' => 'radiohead']).'"', false)
        ->assertDontSee('href="'.route('releases.show', ['slug' => 'ok-computer']).'"', false);
});

it('renders an explicit empty state', function (): void {
    $this->get('/search?q=khong-co-noi-dung')
        ->assertOk()
        ->assertSee('Không tìm thấy “khong-co-noi-dung”')
        ->assertSee('Báo thiếu nội dung');
});

it('validates unsupported search filters', function (): void {
    $this->get('/search?q=test&type=playlist')->assertSessionHasErrors('type');
});

it('renders entity detail and provider disclosure', function (): void {
    $this->get('/releases/ok-computer')
        ->assertOk()
        ->assertSee('OK Computer')
        ->assertSee('Chọn nơi nghe')
        ->assertSee('Trước khi rời SongChart:')
        ->assertSee('Chưa xác định');
});

it('returns not found for unknown canonical entities', function (): void {
    $this->get('/artists/not-found')->assertNotFound();
});

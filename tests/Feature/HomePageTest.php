<?php

declare(strict_types=1);

test('home page is available', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('Bạn muốn tìm gì hôm nay?')
        ->assertSee('href="#main-content"', false)
        ->assertSee('Bỏ qua điều hướng và đến nội dung chính')
        ->assertSee('id="main-content"', false)
        ->assertSee('tabindex="-1"', false);
});

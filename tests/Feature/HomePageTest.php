<?php

declare(strict_types=1);

test('home page is available', function (): void {
    $this->get('/')->assertOk()->assertSee('Bạn muốn tìm gì hôm nay?');
});

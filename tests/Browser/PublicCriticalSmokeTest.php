<?php

declare(strict_types=1);

it('renders the public home surface on desktop without browser smoke errors', function (): void {
    visit('/')
        ->assertSee('Bạn muốn tìm gì hôm nay?')
        ->assertNoSmoke();
});

it('renders the public home surface on mobile without browser smoke errors', function (): void {
    visit('/')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Bạn muốn tìm gì hôm nay?')
        ->assertNoSmoke();
});

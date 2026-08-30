<?php

declare(strict_types=1);

it('renders a release-ready public not found state', function (): void {
    $response = $this->get('/this-public-route-does-not-exist');

    $response->assertNotFound()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('id="main-content"', false)
        ->assertSee('id="not-found-title"', false)
        ->assertSee('Trang này không còn ở đây')
        ->assertSee('Về trang chủ')
        ->assertSee('Tìm trong SongChart')
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('search').'"', false);

    expect(substr_count($response->getContent(), '<main'))->toBe(1)
        ->and(substr_count($response->getContent(), '<h1'))->toBe(1);
});

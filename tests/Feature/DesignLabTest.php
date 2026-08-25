<?php

declare(strict_types=1);

test('design lab index lists ten concepts', function (): void {
    $this->get('/development/design-system/concepts')
        ->assertOk()
        ->assertSee('10 hướng giao diện')
        ->assertSee('Editorial Library')
        ->assertSee('Provider First');
});

test('each design concept is available', function (string $concept): void {
    $this->get("/development/design-system/concepts/{$concept}")->assertOk();
})->with([
    '01-editorial-library',
    '02-search-first',
    '03-artwork-gallery',
    '04-knowledge-graph',
    '05-calm-minimal',
    '06-music-magazine',
    '07-cinematic-dark',
    '08-utility-discovery',
    '09-community-shelves',
    '10-provider-first',
]);

test('design concepts are reachable from the consolidated development design-system surface', function (): void {
    $this->get('/development/design-system/concepts')
        ->assertOk()
        ->assertSee('Editorial Library');

    $this->get('/development/design-system/concepts/01-editorial-library')->assertOk();
});

test('retired design-lab aliases stay removed', function (): void {
    $this->get('/design-lab')->assertNotFound();
    $this->get('/design-lab/01-editorial-library')->assertNotFound();
});

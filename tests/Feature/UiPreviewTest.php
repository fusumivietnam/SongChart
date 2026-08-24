<?php

declare(strict_types=1);

it('renders every governed UI preview section without database data', function (string $section, string $copy): void {
    $this->get("/ui-preview/{$section}")
        ->assertOk()
        ->assertSee($copy)
        ->assertSee('noindex,nofollow', false)
        ->assertSee('Local/staging only');
})->with([
    ['foundations', 'Semantic colors'],
    ['components', 'Buttons'],
    ['patterns', 'Chọn nơi nghe'],
    ['states', 'Dữ liệu chưa hoàn chỉnh'],
    ['admin', 'Công việc cần xử lý'],
]);

it('uses foundations as the default UI preview section', function (): void {
    $this->get('/ui-preview')->assertOk()->assertSee('Semantic colors');
});

it('returns not found for an unknown UI preview section', function (): void {
    $this->get('/ui-preview/unknown')->assertNotFound();
});

it('exposes the consolidated development design-system entry route', function (): void {
    $this->get('/development/design-system')
        ->assertOk()
        ->assertSee('Semantic colors');
});

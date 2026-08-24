<?php

declare(strict_types=1);

it('exposes the canonical verification entry point in repository docs', function (): void {
    $root = dirname(__DIR__, 2);
    $readme = (string) file_get_contents($root.'/README.md');
    $authority = (string) file_get_contents($root.'/PROJECT_AUTHORITY.md');

    expect($readme)->toContain('verify-songchart.bat')
        ->and($authority)->toContain('compose.verify.yml')
        ->and($authority)->toContain('PostgreSQL 18');
});

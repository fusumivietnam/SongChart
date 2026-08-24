<?php

declare(strict_types=1);

use App\Support\Testing\TestDatabaseSafetyPolicy;

it('refuses to use the development PostgreSQL database as the test database', function (): void {
    $errors = (new TestDatabaseSafetyPolicy)->validateNames('songchart', 'songchart');

    expect($errors)->toContain('PostgreSQL test database "songchart" is the same as the development database.')
        ->and($errors)->toContain('PostgreSQL test database "songchart" must end with _test.');
});

it('accepts a dedicated PostgreSQL test database', function (): void {
    expect((new TestDatabaseSafetyPolicy)->validateNames('songchart', 'songchart_test'))->toBe([]);
});

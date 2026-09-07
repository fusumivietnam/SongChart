<?php

declare(strict_types=1);

use App\Support\Development\DevelopmentDatabaseAuthority;
use RuntimeException;

it('accepts explicit local development database mode', function (): void {
    DevelopmentDatabaseAuthority::assertSafe([
        'mode' => 'local',
    ]);

    expect(true)->toBeTrue();
});

it('rejects an undeclared development database mode', function (): void {
    expect(fn () => DevelopmentDatabaseAuthority::assertSafe([
        'mode' => '',
    ]))->toThrow(
        RuntimeException::class,
        'must explicitly be local or remote',
    );
});

it('requires a database url in remote mode', function (): void {
    expect(fn () => DevelopmentDatabaseAuthority::assertSafe([
        'mode' => 'remote',
        'url' => '',
        'expected_database' => 'songchart',
        'sslmode' => 'require',
    ]))->toThrow(
        RuntimeException::class,
        'requires DB_URL',
    );
});

it('rejects local postgres hosts in remote mode', function (string $url): void {
    expect(fn () => DevelopmentDatabaseAuthority::assertSafe([
        'mode' => 'remote',
        'url' => $url,
        'expected_database' => 'songchart',
        'sslmode' => 'require',
    ]))->toThrow(
        RuntimeException::class,
        'must not resolve to a local PostgreSQL host',
    );
})->with([
    'localhost' => 'postgresql://songchart:secret@localhost:5432/songchart',
    'loopback' => 'postgresql://songchart:secret@127.0.0.1:5432/songchart',
    'compose postgres' => 'postgresql://songchart:secret@postgres:5432/songchart',
]);

it('requires an expected database identity in remote mode', function (): void {
    expect(fn () => DevelopmentDatabaseAuthority::assertSafe([
        'mode' => 'remote',
        'url' => 'postgresql://songchart:secret@example.neon.tech/songchart',
        'expected_database' => '',
        'sslmode' => 'require',
    ]))->toThrow(
        RuntimeException::class,
        'requires an expected database identity',
    );
});

it('requires tls in remote mode', function (): void {
    expect(fn () => DevelopmentDatabaseAuthority::assertSafe([
        'mode' => 'remote',
        'url' => 'postgresql://songchart:secret@example.neon.tech/songchart',
        'expected_database' => 'songchart',
        'sslmode' => 'prefer',
    ]))->toThrow(
        RuntimeException::class,
        'requires PostgreSQL TLS',
    );
});

it('accepts a durable tls remote database with explicit identity', function (): void {
    DevelopmentDatabaseAuthority::assertSafe([
        'mode' => 'remote',
        'url' => 'postgresql://songchart:secret@example.neon.tech/songchart',
        'expected_database' => 'songchart',
        'sslmode' => 'require',
    ]);

    expect(true)->toBeTrue();
});

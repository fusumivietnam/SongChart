<?php

declare(strict_types=1);

use App\Support\Development\DevelopmentStorageAuthority;
use RuntimeException;

it('accepts explicit local development storage mode without an s3 adapter', function (): void {
    DevelopmentStorageAuthority::assertSafe([
        'mode' => 'local',
        'disk' => 'local',
    ], false);

    expect(true)->toBeTrue();
});

it('requires the governed s3 adapter in remote mode', function (): void {
    expect(fn () => DevelopmentStorageAuthority::assertSafe([
        'mode' => 'remote',
        'disk' => 'r2',
        'bucket' => 'songchart-development',
        'endpoint' => 'https://example.r2.cloudflarestorage.com',
        'key' => 'key',
        'secret' => 'secret',
    ], false))->toThrow(RuntimeException::class, 'requires the governed Laravel S3 filesystem adapter');
});

it('requires complete r2 configuration in remote mode', function (string $missing): void {
    $configuration = [
        'mode' => 'remote',
        'disk' => 'r2',
        'bucket' => 'songchart-development',
        'endpoint' => 'https://example.r2.cloudflarestorage.com',
        'key' => 'key',
        'secret' => 'secret',
    ];

    $configuration[$missing] = '';

    expect(fn () => DevelopmentStorageAuthority::assertSafe($configuration, true))
        ->toThrow(RuntimeException::class, "requires [{$missing}]");
})->with(['disk', 'bucket', 'endpoint', 'key', 'secret']);

it('requires the governed r2 disk in remote mode', function (): void {
    expect(fn () => DevelopmentStorageAuthority::assertSafe([
        'mode' => 'remote',
        'disk' => 'local',
        'bucket' => 'songchart-development',
        'endpoint' => 'https://example.r2.cloudflarestorage.com',
        'key' => 'key',
        'secret' => 'secret',
    ], true))->toThrow(RuntimeException::class, 'must use the governed [r2] Laravel filesystem disk');
});

it('rejects insecure or local remote endpoints', function (string $endpoint): void {
    expect(fn () => DevelopmentStorageAuthority::assertSafe([
        'mode' => 'remote',
        'disk' => 'r2',
        'bucket' => 'songchart-development',
        'endpoint' => $endpoint,
        'key' => 'key',
        'secret' => 'secret',
    ], true))->toThrow(RuntimeException::class);
})->with([
    'insecure endpoint' => 'http://example.r2.cloudflarestorage.com',
    'localhost' => 'https://localhost:9000',
    'loopback' => 'https://127.0.0.1:9000',
]);

it('accepts a complete remote r2 authority', function (): void {
    DevelopmentStorageAuthority::assertSafe([
        'mode' => 'remote',
        'disk' => 'r2',
        'bucket' => 'songchart-development',
        'endpoint' => 'https://example.r2.cloudflarestorage.com',
        'key' => 'key',
        'secret' => 'secret',
    ], true);

    expect(true)->toBeTrue();
});

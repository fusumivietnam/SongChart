<?php

declare(strict_types=1);

it('defines a canonical Docker verification environment with PostgreSQL 18', function (): void {
    $root = dirname(__DIR__, 2);
    $compose = (string) file_get_contents($root.'/compose.verify.yml');
    $dockerfile = (string) file_get_contents($root.'/docker/verify/Dockerfile');
    $registry = json_decode((string) file_get_contents($root.'/docs/project/stack/package-registry.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($compose)
        ->toContain('postgres:18.4-bookworm')
        ->toContain('songchart_verify_vendor:/workspace/vendor')
        ->toContain('songchart_verify_node_modules:/workspace/node_modules')
        ->toContain('condition: service_healthy')
        ->and($dockerfile)
        ->toContain('FROM php:8.5-cli-bookworm')
        ->toContain('FROM node:22-bookworm-slim AS node')
        ->toContain('pdo_pgsql')
        ->and($registry['runtime']['database'])->toBe('postgresql-18')
        ->and($registry['runtime']['database_major'])->toBe(18);
});

it('normalizes with the locked formatter before release verification', function (): void {
    $root = dirname(__DIR__, 2);
    $script = (string) file_get_contents($root.'/scripts/canonical-verify.sh');

    $normalize = strpos($script, 'composer quality:normalize');
    $canonical = strpos($script, 'composer canonical:verify');

    expect(is_int($normalize))->toBeTrue()
        ->and(is_int($canonical))->toBeTrue();

    if (! is_int($normalize) || ! is_int($canonical)) {
        throw new RuntimeException('Canonical verification commands are missing.');
    }

    expect($normalize)->toBeLessThan($canonical)
        ->and(substr_count($script, 'composer canonical:verify'))->toBe(1)
        ->and($script)->not->toContain('composer release:verify');
});

it('does not rebuild PHP core extensions already provided by the base image', function (): void {
    $root = dirname(__DIR__, 2);
    $dockerfile = (string) file_get_contents($root.'/docker/verify/Dockerfile');

    preg_match('/docker-php-ext-install(?<block>.*?)&& pecl install redis/s', $dockerfile, $matches);
    $extensionInstallBlock = (string) ($matches['block'] ?? '');

    expect($extensionInstallBlock)->not->toBe('');

    foreach (['curl', 'dom', 'mbstring', 'xml'] as $extension) {
        expect(preg_match('/\b'.preg_quote($extension, '/').'\b/', $extensionInstallBlock))
            ->toBe(0);
    }

    expect($dockerfile)
        ->toContain('PHP extension smoke check passed.')
        ->and((string) file_get_contents($root.'/scripts/canonical-verify.sh'))
        ->toContain('php scripts/verify-canonical-php-extensions.php');
});

<?php

declare(strict_types=1);

it('keeps package and candidate governance machine readable', function (): void {
    $root = dirname(__DIR__, 2);
    $registry = json_decode((string) file_get_contents($root.'/docs/project/stack/package-registry.json'), true, 512, JSON_THROW_ON_ERROR);
    $contract = json_decode((string) file_get_contents($root.'/docs/project/stack/candidate-verification-contract.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($registry['runtime']['php'])->toBe('^8.5')
        ->and($registry['runtime']['release_database'])->toBe('pgsql')
        ->and($registry['policies']['new_stage_errors_may_enter_phpstan_baseline'])->toBeFalse()
        ->and($registry['packages'])->toHaveKey('laravel/pulse')
        ->and($contract['required_gates'])->toContain('phpstan', 'postgresql', 'release_verify');
});

it('keeps plain unit tests independent from Laravel application helpers', function (): void {
    $root = dirname(__DIR__, 2);
    $forbidden = ['app()', 'config(', 'base_path(', 'DB::', 'Cache::', 'Http::', 'Storage::', 'Artisan::'];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/tests/Unit'));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());
        foreach ($forbidden as $signal) {
            expect(str_contains($source, $signal), $file->getPathname().' contains '.$signal)->toBeFalse();
        }
    }
});

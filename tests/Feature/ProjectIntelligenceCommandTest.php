<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

it('exposes project intelligence through the Laravel command surface', function (): void {
    $exitCode = Artisan::call('project:intelligence', ['--json' => true]);

    expect($exitCode)->toBe(0);

    $snapshot = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    expect($snapshot['generated_from_repository'] ?? false)->toBeTrue()
        ->and($snapshot['snapshot']['consistency'] ?? null)->toBe('eventual')
        ->and($snapshot['metrics']['class_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['metrics']['route_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['contracts']['kernel'] ?? null)->toBe('docs/project/engineering/project-kernel-contract.json');
});

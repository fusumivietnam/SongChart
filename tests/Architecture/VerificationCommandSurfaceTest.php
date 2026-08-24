<?php

declare(strict_types=1);

it('keeps the active verification command surface bounded', function (): void {
    $root = dirname(__DIR__, 2);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $contract = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/verification-command-surface.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $scripts = $composer['scripts'] ?? [];

    foreach (array_keys($contract['removed_aliases']) as $removed) {
        expect(array_key_exists($removed, $scripts))->toBeFalse();
    }

    expect($scripts)->toHaveKeys([
        'stage:verify',
        'canonical:verify',
        'release:package',
        'verification-surface:verify',
    ]);

    foreach ($contract['historical_orchestration']['retired_files'] as $relative) {
        expect(is_file($root.'/'.$relative))->toBeFalse();
    }
});

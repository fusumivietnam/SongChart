<?php

declare(strict_types=1);

it('keeps stage and canonical verification as single owned pipelines', function (): void {
    $root = dirname(__DIR__, 2);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $topology = json_decode((string) file_get_contents($root.'/docs/project/engineering/verification-topology.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['stage:verify'])->toBe($topology['lanes']['stage']['ordered_steps'])
        ->and($composer['scripts']['canonical:verify'])->toBe($topology['lanes']['canonical']['ordered_steps'])
        ->and(array_key_exists('release:verify', $composer['scripts']))->toBeFalse()
        ->and(array_key_exists('verify', $composer['scripts']))->toBeFalse()
        ->and(count(array_keys($composer['scripts']['canonical:verify'], '@stage:verify', true)))->toBe(1);
});

it('keeps the canonical shell preparation-only outside the single closure entrypoint', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 2).'/scripts/canonical-verify.sh');

    expect(substr_count($source, 'composer canonical:verify'))->toBe(1)
        ->and(str_contains($source, 'composer quality:verify'))->toBeFalse()
        ->and(str_contains($source, 'composer test:postgres'))->toBeFalse()
        ->and(str_contains($source, 'npm run build'))->toBeFalse();
});

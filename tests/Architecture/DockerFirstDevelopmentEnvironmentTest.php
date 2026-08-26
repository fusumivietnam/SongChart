<?php

declare(strict_types=1);

it('makes Docker the primary development authority and retires Laragon', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = json_decode((string) file_get_contents($root.'/docs/project/stack/docker-development-contract.json'), true, 512, JSON_THROW_ON_ERROR);
    $runtime = json_decode((string) file_get_contents($root.'/docs/project/stack/runtime-environments.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($contract['primary_local_runtime'])->toBe('docker-development')
        ->and($runtime['primary_development_profile'])->toBe('docker-development')
        ->and(array_key_exists('laragon-development', $runtime['profiles'] ?? []))->toBeFalse();
});

it('keeps one Linux CLI over isolated development and verification compose lanes', function (): void {
    $root = dirname(__DIR__, 2);
    $cli = (string) file_get_contents($root.'/songchart');
    $verify = (string) file_get_contents($root.'/compose.verify.yml');

    expect($cli)
        ->toContain('compose.dev.yml')
        ->toContain('compose.verify.yml')
        ->toContain('docker-stage-verify.sh')
        ->toContain('songchart-verify')
        ->and($verify)->toContain('songchart_verify_test')
        ->and(str_contains($verify, 'songchart_docker'))->toBeFalse();
});

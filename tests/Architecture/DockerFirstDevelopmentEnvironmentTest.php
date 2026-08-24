<?php

declare(strict_types=1);

it('makes Docker the primary development authority and Laragon compatibility only', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = json_decode((string) file_get_contents($root.'/docs/project/stack/docker-development-contract.json'), true, 512, JSON_THROW_ON_ERROR);
    $runtime = json_decode((string) file_get_contents($root.'/docs/project/stack/runtime-environments.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($contract['primary_local_runtime'])->toBe('docker-development')
        ->and($runtime['primary_development_profile'])->toBe('docker-development')
        ->and($runtime['profiles']['laragon-development']['authority'])->toBe('compatibility-only')
        ->and($runtime['profiles']['laragon-development']['release_claims_allowed'])->toBeFalse();
});

it('keeps one Windows CLI over isolated dev and verification compose lanes', function (): void {
    $root = dirname(__DIR__, 2);
    $cli = (string) file_get_contents($root.'/scripts/songchart.ps1');
    $verify = (string) file_get_contents($root.'/compose.verify.yml');

    expect($cli)->toContain('compose.dev.yml', 'compose.verify.yml', 'docker-stage-verify.sh', 'verify-canonical.ps1')
        ->and($verify)->toContain('songchart_verify_test')
        ->and(str_contains($verify, 'songchart_docker'))->toBeFalse();
});

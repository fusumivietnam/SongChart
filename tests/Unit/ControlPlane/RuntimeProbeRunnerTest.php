<?php

declare(strict_types=1);

use App\Support\ControlPlane\RuntimeProbeRunner;

$repositoryRoot = dirname(__DIR__, 3);

it('reports successful runtime probes without exposing process output', function () use ($repositoryRoot): void {
    $secret = 'runtime-probe-secret-sentinel';
    $result = (new RuntimeProbeRunner())->run(
        [PHP_BINARY, '-r', 'fwrite(STDOUT, '.var_export($secret, true).'); exit(0);'],
        'test:success',
        $repositoryRoot,
        5,
    );

    expect($result)
        ->toMatchArray([
            'status' => 'ready',
            'owner' => 'test:success',
            'exit_code' => 0,
            'timed_out' => false,
            'secrets_included' => false,
        ])
        ->and(json_encode($result, JSON_THROW_ON_ERROR))->not->toContain($secret);
});

it('fails closed when a runtime probe returns a non-zero exit code', function () use ($repositoryRoot): void {
    $result = (new RuntimeProbeRunner())->run(
        [PHP_BINARY, '-r', 'fwrite(STDERR, "sensitive diagnostic"); exit(7);'],
        'test:failure',
        $repositoryRoot,
        5,
    );

    expect($result)
        ->toMatchArray([
            'status' => 'blocked',
            'owner' => 'test:failure',
            'exit_code' => 7,
            'timed_out' => false,
            'secrets_included' => false,
        ])
        ->and(json_encode($result, JSON_THROW_ON_ERROR))->not->toContain('sensitive diagnostic');
});

it('fails closed when a runtime probe exceeds its bounded timeout', function () use ($repositoryRoot): void {
    $result = (new RuntimeProbeRunner())->run(
        [PHP_BINARY, '-r', 'sleep(2);'],
        'test:timeout',
        $repositoryRoot,
        1,
    );

    expect($result)
        ->toMatchArray([
            'status' => 'blocked',
            'owner' => 'test:timeout',
            'exit_code' => null,
            'timed_out' => true,
            'secrets_included' => false,
        ]);
});

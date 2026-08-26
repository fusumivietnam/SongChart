<?php

declare(strict_types=1);

it('keeps candidate read-only and exposes optimized closure and demo workflows', function (): void {
    $cli = file_get_contents(base_path('songchart'));
    $demo = file_get_contents(base_path('compose.demo.yml'));

    expect($cli)
        ->toContain('./songchart close')
        ->toContain('canonical owns the stage lane exactly once')
        ->toContain('./songchart dev test --no-build <path>')
        ->toContain('./songchart dev db backup')
        ->toContain('./songchart demo setup')
        ->toContain('demo up -d redis app')
        ->not->toContain('demo up -d redis app queue');

    $candidateBlock = str($cli)->after(" candidate)\n")->before(" close)\n")->toString();
    expect($candidateBlock)
        ->toContain('git -C "$ROOT" diff --check')
        ->not->toContain('refresh_candidate_authority');

    $closeBlock = str($cli)->after(" close)\n")->before(" test)\n")->toString();
    expect($closeBlock)
        ->toContain('prepare_candidate_authority')
        ->toContain('verify_compose run --rm verify')
        ->not->toContain('stage_verify');

    expect($demo)
        ->toContain('DB_DATABASE: songchart_docker')
        ->toContain('name: "${SONGCHART_DEV_PROJECT:-songchart-dev}_default"')
        ->not->toContain('songchart_verify_test');
});

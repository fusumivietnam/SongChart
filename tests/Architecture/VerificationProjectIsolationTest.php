<?php

declare(strict_types=1);

it('isolates canonical verification from the long lived development compose project', function (): void {
    $root = dirname(__DIR__, 2);
    $cli = (string) file_get_contents($root.'/songchart');
    $compose = (string) file_get_contents($root.'/compose.verify.yml');

    expect($cli)
        ->toContain('VERIFY_PROJECT="${SONGCHART_VERIFY_PROJECT:-songchart-verify}"')
        ->toContain('docker compose -p "$VERIFY_PROJECT" -f "$VERIFY_COMPOSE"')
        ->and($compose)
        ->toContain('songchart_verify_test')
        ->not->toContain('songchart_dev_pgdata');
});

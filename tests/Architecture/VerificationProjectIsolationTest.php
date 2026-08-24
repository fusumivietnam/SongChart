<?php

declare(strict_types=1);

it('isolates canonical verification from the long lived development compose project', function (): void {
    $root = dirname(__DIR__, 2);
    $script = (string) file_get_contents($root.'/scripts/verify-canonical.ps1');

    expect($script)->toContain("\$ProjectName = 'songchart-verify'")
        ->and(substr_count($script, '-p $ProjectName -f $Compose'))->toBeGreaterThanOrEqual(4);
});

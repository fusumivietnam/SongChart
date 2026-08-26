<?php

declare(strict_types=1);

it('exposes machine readable project context through the canonical Linux CLI', function (): void {
    $root = dirname(__DIR__, 2);
    $cli = (string) file_get_contents($root.'/songchart');
    $protocol = (string) file_get_contents($root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md');

    expect($cli)
        ->toContain('context)')
        ->toContain('scripts/project-context.php')
        ->toContain('songchart-verify')
        ->toContain('--refresh-source')
        ->toContain('--write-source')
        ->and($protocol)
        ->toContain('songchart context --json')
        ->toContain('Do not infer class names')
        ->toContain('report drift');
});

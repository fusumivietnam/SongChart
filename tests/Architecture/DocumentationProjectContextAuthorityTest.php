<?php

declare(strict_types=1);

it('exposes machine readable project context through the canonical local cli', function (): void {
    $root = dirname(__DIR__, 2);
    $cli = (string) file_get_contents($root.'/scripts/songchart.ps1');
    $protocol = (string) file_get_contents($root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md');

    expect($cli)
        ->toContain("'context'")
        ->toContain('project-context.php')
        ->toContain('songchart-verify')
        ->toContain('[string]::IsNullOrWhiteSpace($Subcommand)')
        ->toContain('$contextArgs.Count -gt 0')
        ->and($protocol)
        ->toContain('songchart context --json')
        ->toContain('Do not infer class names')
        ->toContain('report drift');
});

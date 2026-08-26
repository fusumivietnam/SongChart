<?php

declare(strict_types=1);

it('keeps canonical verification evidence outside the tracked candidate definition', function (): void {
    $recorder = file_get_contents(base_path('scripts/record-canonical-verification.php'));
    $verifier = file_get_contents(base_path('scripts/verify-candidate-contract.php'));

    expect($recorder)
        ->toContain('storage/framework/candidate-verification-runtime.json')
        ->toContain('status --porcelain --untracked-files=no')
        ->toContain('refusing to record closure evidence')
        ->toContain('$runtimePath,')
        ->not->toContain('$definitionPath,');

    expect($verifier)
        ->toContain('storage/framework/candidate-verification-runtime.json')
        ->toContain('rev-parse HEAD')
        ->toContain('$runtimeCommit === $head')
        ->toContain('$runtimeDirty === null');
});

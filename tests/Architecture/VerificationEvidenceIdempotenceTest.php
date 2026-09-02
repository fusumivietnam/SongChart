<?php

declare(strict_types=1);

it('keeps canonical verification evidence outside the tracked candidate definition', function (): void {
    $recorder = file_get_contents(base_path('scripts/record-canonical-verification.php'));
    $verifier = file_get_contents(base_path('scripts/verify-candidate-contract.php'));
    $status = file_get_contents(base_path('scripts/ai-status.sh'));
    $projectState = file_get_contents(base_path('scripts/project-state.php'));
    $gitignore = file_get_contents(base_path('.gitignore'));

    expect($recorder)
        ->toContain('storage/framework/candidate-verification-runtime.json')
        ->toContain('status --porcelain --untracked-files=no')
        ->toContain('refusing to record closure evidence')
        ->toContain('$runtimePath,')
        ->not->toContain('$definitionPath,');

    expect($verifier)
        ->toContain('storage/framework/candidate-verification-runtime.json')
        ->toContain('docs/project/generated/development-state.json')
        ->toContain('rev-parse HEAD')
        ->toContain('$runtimeCommit === $head')
        ->toContain('$runtimeDirty === null');

    expect($status)
        ->toContain('project-state.php')
        ->toContain('--json')
        ->not->toContain('candidate-verification-runtime.json');

    expect($projectState)
        ->toContain("'source' => 'git-and-github-runtime'")
        ->toContain("'head_sha' =>")
        ->toContain("'pr_number' =>")
        ->toContain("'resume_rule' =>");

    expect($gitignore)
        ->toContain('/storage/framework/*')
        ->not->toContain('/storage/framework/candidate-verification-runtime.json');
});

<?php

declare(strict_types=1);

it('exposes the dependency-aware workflow golden path', function (): void {
    $cli = (string) file_get_contents(base_path('songchart'));
    $impact = (string) file_get_contents(base_path('scripts/resolve-repository-impact.php'));
    $impactRunner = (string) file_get_contents(base_path('scripts/run-impact-verification.sh'));
    $audit = (string) file_get_contents(base_path('scripts/run-workflow-audit.php'));

    expect($cli)
        ->toContain('./songchart impact [--diff|--verify] [--json]')
        ->toContain('./songchart impact --verify')
        ->toContain('scripts/run-impact-verification.sh')
        ->toContain('./songchart reconcile')
        ->toContain('./songchart audit')
        ->toContain('Regenerating repository authority from the current exact working tree')
        ->toContain('git -C "$ROOT" --no-pager diff -- docs/project/generated')
        ->toContain('collect all quality/static/governance failures without fail-fast');

    expect($impact)
        ->toContain("if (\$arg === '--diff')")
        ->toContain('matched_impact_rules')
        ->toContain('reverse_verification_consumers')
        ->toContain('required_focused_checks')
        ->toContain("'origin/main'")
        ->toContain("git -C '.escapeshellarg(\$root).' ls-files --others --exclude-standard");

    expect($impactRunner)
        ->toContain('required_focused_checks')
        ->toContain('closure_checks=()')
        ->toContain("'composer canonical:verify'|'songchart verify'|'songchart close'")
        ->toContain('Deferred closure-only checks')
        ->toContain('Canonical verification is closure-only; run it through candidate/close on an exact committed tree.')
        ->toContain("'composer stage:verify'")
        ->toContain('run_test_target')
        ->toContain('git -C "$ROOT" ls-files -- "$target"')
        ->not->toContain('"$SONGCHART" verify')
        ->not->toContain('eval ');

    expect($audit)
        ->toContain("\$composer['scripts']['quality:verify']")
        ->toContain('[SongChart audit] FAILURES')
        ->toContain("'@stage:verify'")
        ->toContain("'@canonical:verify'")
        ->toContain("'@test:postgres'")
        ->toContain("'npm run build'");
});

it('guards impact targets and runtime-only artifacts before canonical closure', function (): void {
    $impactVerifier = (string) file_get_contents(base_path('scripts/verify-impact-test-map.php'));
    $runtimeVerifier = (string) file_get_contents(base_path('scripts/verify-runtime-artifact-ownership.php'));
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($impactVerifier)
        ->toContain('verification-topology.json')
        ->toContain('removed_aliases')
        ->toContain('references retired Composer command')
        ->toContain('references missing Composer command')
        ->toContain('references missing test target');

    expect($runtimeVerifier)
        ->toContain("'storage/framework/'")
        ->toContain("'storage/logs/'")
        ->toContain('git -C ')
        ->toContain('ls-files -z');

    expect($composer['scripts']['runtime-artifact:verify'] ?? null)
        ->toBe('@php scripts/verify-runtime-artifact-ownership.php')
        ->and($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@runtime-artifact:verify');
});

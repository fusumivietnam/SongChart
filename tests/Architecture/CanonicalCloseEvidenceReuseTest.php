<?php

declare(strict_types=1);

it('reuses exact-head stage evidence only inside auto closure close', function (): void {
    $root = base_path();
    $topology = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/verification-topology.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $autoClosure = (string) file_get_contents($root.'/.github/workflows/auto-closure.yml');
    $compose = (string) file_get_contents($root.'/compose.verify.yml');
    $shell = (string) file_get_contents($root.'/scripts/canonical-verify.sh');
    $adapter = (string) file_get_contents($root.'/scripts/run-canonical-close.php');

    expect($topology['evidence_reuse']['enabled'] ?? null)->toBeTrue()
        ->and($topology['evidence_reuse']['scope'] ?? null)->toBe('Auto Closure only')
        ->and($topology['lanes']['ci_close']['reuse_step'] ?? null)->toBe('@stage:verify')
        ->and($autoClosure)->toContain('SONGCHART_VERIFICATION_MODE: close')
        ->and($autoClosure)->toContain('needs:')
        ->and($autoClosure)->toContain('- verify')
        ->and($compose)->toContain('SONGCHART_VERIFICATION_MODE: "${SONGCHART_VERIFICATION_MODE:-full}"')
        ->and($shell)->toContain('mode="${SONGCHART_VERIFICATION_MODE:-full}"')
        ->and($shell)->toContain('php scripts/run-canonical-close.php')
        ->and($shell)->toContain('composer canonical:verify')
        ->and($adapter)->toContain("if (\$step === '@stage:verify')")
        ->and($adapter)->toContain("['composer', '--no-interaction', 'run-script', \$script]");
});

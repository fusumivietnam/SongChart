<?php

declare(strict_types=1);

it('keeps PostgreSQL tests isolated from the development database', function (): void {
    $runner = (string) file_get_contents(base_path('scripts/run-database-tests.php'));
    $safety = (string) file_get_contents(base_path('scripts/verify-test-database-safety.php'));
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

    $topology = json_decode((string) file_get_contents(base_path('docs/project/engineering/verification-topology.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($runner)->not->toContain("'TEST_PGSQL_DATABASE', 'DB_DATABASE'")
        ->and($runner)->toContain('verify-test-database-safety.php')
        ->and($safety)->toContain('Refusing to run destructive PostgreSQL tests')
        ->and($composer['scripts'])->not->toHaveKey('release:verify')
        ->and($composer['scripts']['canonical:verify'] ?? null)->toBe($topology['lanes']['canonical']['ordered_steps']);

    $canonical = $composer['scripts']['canonical:verify'];
    expect(array_search('@test-database:safety', $canonical, true))
        ->toBeLessThan(array_search('@stage:verify', $canonical, true));
});

it('keeps development two factor bypass bounded to approved non-production runtimes', function (): void {
    $middleware = (string) file_get_contents(base_path('app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php'));

    expect($middleware)
        ->toContain('$twoFactorMode = (string) config(\'songchart.security.admin_2fa_mode\', \'required\')')
        ->toContain("app()->environment(['local', 'demo', 'testing'])")
        ->toContain('$requiresTwoFactor = $twoFactorMode === \'required\' || ! $mayDisableTwoFactor');
});

it('fails closed for durable remote development database configuration', function (): void {
    $authority = (string) file_get_contents(base_path('app/Support/Development/DevelopmentDatabaseAuthority.php'));
    $status = (string) file_get_contents(base_path('app/Console/Commands/DevelopmentDatabaseStatusCommand.php'));
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/engineering/development-database-contract.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($authority)
        ->toContain('Development database mode must explicitly be local or remote.')
        ->toContain('Remote development database mode requires DB_URL.')
        ->toContain('must not resolve to a local PostgreSQL host')
        ->toContain('requires PostgreSQL TLS')
        ->and($status)
        ->toContain('current_database() as database_name')
        ->toContain('Development database identity mismatch')
        ->and($contract['operations']['must_verify_runtime_identity_before_mutation'] ?? null)->toBeTrue()
        ->and($contract['testing']['must_not_reuse_development_database'] ?? null)->toBeTrue();
});

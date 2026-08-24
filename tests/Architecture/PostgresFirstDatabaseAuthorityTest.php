<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

it('keeps PostgreSQL mandatory as the release database authority', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
    $contract = (new RepositoryContractResolver(base_path()))->value('database-test');
    $scripts = $composer['scripts'];
    $expectedScripts = $contract['scripts'];

    foreach (['test', 'test:feature', 'test:postgres'] as $script) {
        expect($scripts[$script] ?? null)->toBe($expectedScripts[$script] ?? null);
    }

    $releaseContract = (new RepositoryContractResolver(base_path()))->value('release-pipeline');

    expect($scripts['test:feature'] ?? null)->toContain('run-database-tests.php postgres', '--prepare-schema')
        ->and($scripts['test:postgres'] ?? null)->toContain('run-database-tests.php postgres', '--prepare-schema')
        ->and($scripts['test:sqlite:compat'] ?? null)->toBe('@php scripts/run-database-tests.php sqlite')
        ->and($scripts)->not->toHaveKey('release:verify')
        ->and($scripts['stage:verify'] ?? null)->toBe($releaseContract['stage_steps'])
        ->and($scripts['canonical:verify'] ?? null)->toBe($releaseContract['canonical_steps']);

    $stage = $scripts['stage:verify'] ?? [];
    $canonical = $scripts['canonical:verify'] ?? [];
    expect($stage)->toContain('@test:postgres')
        ->and(in_array('@test:sqlite', $stage, true))->toBeFalse()
        ->and(in_array('@test:sqlite:compat', $stage, true))->toBeFalse()
        ->and(in_array('@test:sqlite', $canonical, true))->toBeFalse()
        ->and(in_array('@test:sqlite:compat', $canonical, true))->toBeFalse()
        ->and(str_contains(implode(' ', $composer['scripts']['post-create-project-cmd'] ?? []), 'database.sqlite'))->toBeFalse();
});

it('does not force SQLite in the base PHPUnit configuration', function (): void {
    $phpunit = (string) file_get_contents(base_path('phpunit.xml'));

    expect(str_contains($phpunit, 'DB_CONNECTION'))->toBeFalse()
        ->and(str_contains($phpunit, 'DB_DATABASE'))->toBeFalse();
});

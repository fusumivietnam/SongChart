<?php

declare(strict_types=1);

it('keeps official database-backed test aliases isolated', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
    $scripts = $composer['scripts'];

    $topology = json_decode((string) file_get_contents(base_path('docs/project/engineering/verification-topology.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($scripts['test'])->toBe('@test:postgres')
        ->and($scripts)->not->toHaveKey('test:all')
        ->and($scripts['test:feature'])->toContain('run-database-tests.php postgres')
        ->and(array_key_exists('release:verify', $scripts))->toBeFalse()
        ->and($scripts['stage:verify'])->toBe($topology['lanes']['stage']['ordered_steps']);

    expect($scripts['stage:verify'])->toContain('@test:postgres')
        ->and(in_array('@test:sqlite', $scripts['stage:verify'], true))->toBeFalse()
        ->and(in_array('@test:sqlite:compat', $scripts['stage:verify'], true))->toBeFalse();
});

it('keeps admin read models free from request-time schema probing', function (): void {
    foreach ([
        'app/Support/Admin/AdminDashboardSnapshot.php',
        'app/Support/Admin/AdminInformationArchitecture.php',
        'app/Support/Admin/CatalogAdministration.php',
        'app/Support/Admin/ProviderOperationsConsole.php',
    ] as $relative) {
        $source = (string) file_get_contents(base_path($relative));
        expect(str_contains($source, 'Schema::hasTable('))->toBeFalse()
            ->and(str_contains($source, 'Facades\\Schema'))->toBeFalse();
    }
});

it('keeps every migration table classified by schema ownership', function (): void {
    $ownership = json_decode((string) file_get_contents(base_path('docs/project/domain/schema-ownership.json')), true, 512, JSON_THROW_ON_ERROR);
    $classified = array_keys($ownership['tables']);
    $framework = $ownership['framework_tables'];

    foreach (glob(base_path('database/migrations/*.php')) ?: [] as $migration) {
        $source = (string) file_get_contents($migration);
        preg_match_all("/Schema::create\\('([^']+)'/", $source, $matches);
        foreach ($matches[1] as $table) {
            expect(in_array($table, $classified, true) || in_array($table, $framework, true))->toBeTrue();
        }
    }
});

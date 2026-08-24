<?php

declare(strict_types=1);

it('keeps PostgreSQL as the release database authority and semantic navigation selectors stable', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
    $scripts = $composer['scripts'];

    $topology = json_decode((string) file_get_contents(base_path('docs/project/engineering/verification-topology.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($scripts['test'])->toBe('@test:postgres')
        ->and($scripts)->not->toHaveKey('test:all')
        ->and($scripts['test:feature'])->toContain('run-database-tests.php postgres')
        ->and(array_key_exists('release:verify', $scripts))->toBeFalse()
        ->and($scripts['stage:verify'])->toBe($topology['lanes']['stage']['ordered_steps'])
        ->toContain('@test:postgres');

    expect(in_array('@test:sqlite:compat', $scripts['stage:verify'], true))->toBeFalse();

    $sidebar = (string) file_get_contents(resource_path('views/components/admin/sidebar.blade.php'));
    $uxTest = (string) file_get_contents(base_path('tests/Feature/AdminOperationsUxTest.php'));

    expect($sidebar)->toContain('data-admin-nav="{{ $item[\'key\'] }}"')
        ->and($uxTest)->toContain('data-admin-nav="imports"');

    expect(str_contains($uxTest, "assertDontSee('Tác vụ dữ liệu')"))->toBeFalse();
});

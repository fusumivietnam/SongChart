<?php

declare(strict_types=1);

it('keeps Horizon optional and custom worker loops out of application code', function (): void {
    $provider = (string) file_get_contents(app_path('Providers/AuthorizationServiceProvider.php'));

    $capabilities = (string) file_get_contents(app_path('Enums/Capability.php'));

    expect(str_contains($provider, 'foreach (Capability::cases() as $capability)'))->toBeTrue()
        ->and(str_contains($capabilities, "case ViewHorizon = 'viewHorizon';"))->toBeTrue()
        ->and(is_file(app_path('Providers/HorizonServiceProvider.php')))->toBeFalse();

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());
        expect(preg_match('/while\s*\(\s*true\s*\).*queue/is', $source))->toBe(0, $file->getPathname());
    }
});

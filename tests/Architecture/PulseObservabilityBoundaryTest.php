<?php

declare(strict_types=1);

it('keeps Pulse out of SongChart domain and application code', function (): void {
    $root = dirname(__DIR__, 2);
    $files = [];

    foreach ([$root.'/app/Domain', $root.'/app/Application'] as $directory) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
    }

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $source = (string) file_get_contents($file);
        expect(str_contains($source, 'Laravel\\Pulse'))->toBeFalse("Pulse coupling found in {$file}")
            ->and(str_contains($source, 'Pulse::record('))->toBeFalse("Custom Pulse recording found in {$file}");
    }
});

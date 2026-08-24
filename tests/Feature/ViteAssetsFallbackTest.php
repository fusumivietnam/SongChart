<?php

declare(strict_types=1);

test('local pages do not fail when the vite manifest has not been built', function (): void {
    $manifest = public_path('build/manifest.json');
    $backup = $manifest.'.test-backup';

    if (is_file($manifest)) {
        rename($manifest, $backup);
    }

    try {
        $this->get('/')
            ->assertOk()
            ->assertSee('Vite assets chưa được build');
    } finally {
        if (is_file($backup)) {
            @mkdir(dirname($manifest), 0777, true);
            rename($backup, $manifest);
        }
    }
});

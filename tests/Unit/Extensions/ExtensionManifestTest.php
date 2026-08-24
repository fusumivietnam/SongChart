<?php

declare(strict_types=1);

use App\Extensions\ExtensionManifest;

it('parses a valid plugin manifest', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'plugin');
    file_put_contents($path, json_encode([
        'type' => 'plugin', 'name' => 'Test', 'slug' => 'vendor/test', 'version' => '1.0.0',
    ], JSON_THROW_ON_ERROR));

    expect(ExtensionManifest::fromFile($path, 'plugin')->slug())->toBe('vendor/test');
    unlink($path);
});

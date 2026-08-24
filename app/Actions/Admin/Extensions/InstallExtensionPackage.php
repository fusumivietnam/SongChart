<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionInstaller;

final readonly class InstallExtensionPackage
{
    public function __construct(private ExtensionInstaller $installer) {}

    public function handle(string $stored, string $type, bool $enable): void
    {
        $this->installer->install(storage_path('app/private/'.$stored), $type, $enable);
    }
}

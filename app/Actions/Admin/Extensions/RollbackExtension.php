<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionUpgradeManager;
use App\Models\Extension;

final readonly class RollbackExtension
{
    public function __construct(private ExtensionUpgradeManager $manager) {}

    public function handle(Extension $extension, ?string $version): void
    {
        $this->manager->rollback($extension->slug, $version !== '' ? $version : null);
    }
}

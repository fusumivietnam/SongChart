<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionUpgradeManager;
use App\Models\Extension;

final readonly class CleanupExtensionReleases
{
    public function __construct(private ExtensionUpgradeManager $manager) {}

    public function handle(Extension $extension, int $keep): int
    {
        return $this->manager->cleanup($extension->slug, $keep);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionManager;
use App\Models\Extension;

final readonly class TogglePlugin
{
    public function __construct(private ExtensionManager $manager) {}

    public function handle(Extension $extension): bool
    {
        abort_unless($extension->type === 'plugin', 422);
        $wasEnabled = $extension->enabled;
        $this->manager->setPluginEnabled($extension->slug, ! $wasEnabled);

        return $wasEnabled;
    }
}

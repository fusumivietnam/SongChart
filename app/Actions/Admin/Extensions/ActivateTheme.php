<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionManager;
use App\Models\Extension;

final readonly class ActivateTheme
{
    public function __construct(private ExtensionManager $manager) {}

    public function handle(Extension $extension): void
    {
        abort_unless($extension->type === 'theme', 422);
        $this->manager->activateTheme($extension->slug);
    }
}

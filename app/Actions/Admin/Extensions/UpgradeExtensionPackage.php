<?php

declare(strict_types=1);

namespace App\Actions\Admin\Extensions;

use App\Extensions\ExtensionUpgradeManager;
use App\Models\Extension;
use Illuminate\Http\UploadedFile;

final readonly class UpgradeExtensionPackage
{
    public function __construct(private ExtensionUpgradeManager $manager) {}

    public function handle(Extension $extension, UploadedFile $package): void
    {
        $stored = $package->store('extensions/uploads');
        $this->manager->upgrade(storage_path('app/private/'.$stored), $extension->type, true);
    }
}

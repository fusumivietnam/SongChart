<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Models\Extension;
use App\Models\ExtensionSnapshot;

final class ExtensionSnapshotManager
{
    public function create(Extension $extension, string $reason): ExtensionSnapshot
    {
        return ExtensionSnapshot::query()->create([
            'extension_id' => $extension->id,
            'reason' => $reason,
            'active_version' => $extension->active_version,
            'active_path' => $extension->active_path,
            'enabled' => $extension->enabled,
            'manifest' => $extension->manifest,
            'configuration' => $extension->configuration ?? [],
            'created_by' => auth()->id(),
        ]);
    }
}

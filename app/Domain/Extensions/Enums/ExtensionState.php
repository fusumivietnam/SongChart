<?php

declare(strict_types=1);

namespace App\Domain\Extensions\Enums;

enum ExtensionState: string
{
    case Uploaded = 'uploaded';
    case Inspecting = 'inspecting';
    case Rejected = 'rejected';
    case Staged = 'staged';
    case Installed = 'installed';
    case Disabled = 'disabled';
    case Enabled = 'enabled';
    case Quarantined = 'quarantined';
}

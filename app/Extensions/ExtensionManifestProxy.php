<?php

declare(strict_types=1);

namespace App\Extensions;

final readonly class ExtensionManifestProxy
{
    /** @param array<string,mixed> $data */
    public function __construct(public array $data, public string $type) {}
}

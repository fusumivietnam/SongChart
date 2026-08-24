<?php

declare(strict_types=1);

namespace App\Extensions;

final readonly class PreflightResult
{
    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     * @param  array<string, mixed>  $facts
     */
    public function __construct(public ExtensionManifest $manifest, public string $checksum, public array $errors, public array $warnings, public array $facts) {}

    public function passed(): bool
    {
        return $this->errors === [];
    }

    public function status(): string
    {
        return $this->passed() ? ($this->warnings === [] ? 'pass' : 'pass_with_warnings') : 'fail';
    }
}

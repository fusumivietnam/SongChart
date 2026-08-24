<?php

declare(strict_types=1);

namespace App\Extensions;

use Composer\Semver\Semver;
use InvalidArgumentException;

final readonly class ExtensionManifest
{
    /** @param array<string, mixed> $data */
    private function __construct(public string $type, public array $data, public string $sourcePath) {}

    public static function fromFile(string $path, string $expectedType): self
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Manifest not found: {$path}");
        }
        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Manifest must be a JSON object.');
        }
        foreach (['name', 'slug', 'version', 'type'] as $required) {
            if (! isset($decoded[$required]) || ! is_string($decoded[$required]) || trim($decoded[$required]) === '') {
                throw new InvalidArgumentException("Manifest field '{$required}' is required.");
            }
        }
        if ($decoded['type'] !== $expectedType) {
            throw new InvalidArgumentException("Expected {$expectedType} manifest, got {$decoded['type']}.");
        }
        if (! preg_match('/^[a-z0-9][a-z0-9-]*\/[a-z0-9][a-z0-9-]*$/', $decoded['slug'])) {
            throw new InvalidArgumentException('Slug must use vendor/package format.');
        }
        if (! preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/', $decoded['version'])) {
            throw new InvalidArgumentException('Version must be semantic versioning.');
        }

        return new self($expectedType, $decoded, $path);
    }

    public function slug(): string
    {
        return $this->data['slug'];
    }

    public function name(): string
    {
        return $this->data['name'];
    }

    public function version(): string
    {
        return $this->data['version'];
    }

    public function coreConstraint(): string
    {
        return (string) ($this->data['requires']['core'] ?? $this->data['core']['constraint'] ?? '^1.0');
    }

    public function phpConstraint(): string
    {
        return (string) ($this->data['requires']['php'] ?? '^8.3');
    }

    /** @return list<string> */
    public function phpExtensions(): array
    {
        return array_values(array_filter((array) ($this->data['requires']['extensions'] ?? []), 'is_string'));
    }

    /** @return array<string,string> */
    public function pluginDependencies(): array
    {
        return (array) ($this->data['requires']['plugins'] ?? []);
    }

    /** @return array<string,string> */
    public function conflicts(): array
    {
        return (array) ($this->data['conflicts']['plugins'] ?? []);
    }

    public function serviceProvider(): ?string
    {
        return isset($this->data['service_provider']) ? (string) $this->data['service_provider'] : null;
    }

    public function namespace(): ?string
    {
        return isset($this->data['autoload']['namespace']) ? (string) $this->data['autoload']['namespace'] : null;
    }

    public function sourceDirectory(): string
    {
        return (string) ($this->data['autoload']['path'] ?? 'src');
    }

    public function supportsCore(string $version): bool
    {
        return Semver::satisfies($version, $this->coreConstraint());
    }

    public function supportsPhp(string $version): bool
    {
        return Semver::satisfies($version, $this->phpConstraint());
    }
}

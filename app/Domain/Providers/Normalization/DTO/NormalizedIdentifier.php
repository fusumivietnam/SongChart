<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use InvalidArgumentException;

final readonly class NormalizedIdentifier
{
    public function __construct(
        public string $namespace,
        public string $value,
    ) {
        if (trim($namespace) === '' || trim($value) === '') {
            throw new InvalidArgumentException('Normalized identifiers require namespace and value.');
        }
    }

    /** @return array{namespace: string, value: string} */
    public function toArray(): array
    {
        return ['namespace' => $this->namespace, 'value' => $this->value];
    }
}

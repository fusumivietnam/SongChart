<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\DTO;

use App\Domain\Providers\Catalog\Enums\ProviderRequestFailureKind;

final readonly class ProviderRequestFailure
{
    /** @param array<string, scalar|null> $context */
    public function __construct(
        public ProviderRequestFailureKind $kind,
        public string $message,
        public ?int $statusCode = null,
        public array $context = [],
    ) {}

    public function retryable(): bool
    {
        return $this->kind->retryable();
    }
}

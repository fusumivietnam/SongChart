<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Validation\DTO;

use App\Domain\Providers\Normalization\Validation\Enums\NormalizationFailureKind;

final readonly class NormalizationValidationIssue
{
    /** @param array<string, mixed> $context */
    public function __construct(
        public NormalizationFailureKind $kind,
        public string $path,
        public string $message,
        public array $context = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind->value,
            'path' => $this->path,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}

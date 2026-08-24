<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Fusion;

final readonly class FieldEvidence
{
    /** @param array<string, mixed> $value */
    public function __construct(
        public string $assertionId,
        public string $sourceKey,
        public string $fieldName,
        public array $value,
        public float $sourceAuthority,
        public float $assertionConfidence,
        public float $freshness,
        public string $verificationState,
        public ?string $observedAt,
    ) {}

    public function score(): float
    {
        return round($this->sourceAuthority * $this->assertionConfidence * $this->freshness, 4);
    }
}

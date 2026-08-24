<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class EnrichmentEvidenceAdmission
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $decision,
        public string $reason,
        public array $payload,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function admissible(string $reason, array $payload): self
    {
        return new self('admissible', $reason, $payload);
    }

    /** @param array<string, mixed> $payload */
    public static function reviewRequired(string $reason, array $payload): self
    {
        return new self('review_required', $reason, $payload);
    }

    /** @param array<string, mixed> $payload */
    public static function rejected(string $reason, array $payload): self
    {
        return new self('rejected', $reason, $payload);
    }
}

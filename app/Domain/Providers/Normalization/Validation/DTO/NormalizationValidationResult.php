<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Validation\DTO;

final readonly class NormalizationValidationResult
{
    /** @param list<NormalizationValidationIssue> $issues */
    public function __construct(public array $issues = []) {}

    public function isValid(): bool
    {
        return $this->issues === [];
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(
            static fn (NormalizationValidationIssue $issue): array => $issue->toArray(),
            $this->issues,
        );
    }
}

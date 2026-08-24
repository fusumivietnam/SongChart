<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enrichment;

final readonly class EnrichmentExecutionResult
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $outcome,
        public ?string $message = null,
        public array $payload = [],
        public ?int $retryAfterSeconds = null,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function succeeded(array $payload = []): self
    {
        return new self('succeeded', payload: $payload);
    }

    public static function retryable(string $message, int $retryAfterSeconds = 1): self
    {
        return new self('retryable', $message, retryAfterSeconds: max(1, $retryAfterSeconds));
    }

    /** @param array<string, mixed> $payload */
    public static function reviewRequired(string $message, array $payload = []): self
    {
        return new self('review_required', $message, $payload);
    }

    public static function failed(string $message): self
    {
        return new self('failed', $message);
    }
}

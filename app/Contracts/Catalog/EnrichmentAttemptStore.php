<?php

declare(strict_types=1);

namespace App\Contracts\Catalog;

use App\Domain\Catalog\Enrichment\EnrichmentDispatch;

interface EnrichmentAttemptStore
{
    public function reserve(EnrichmentDispatch $dispatch): string;

    public function markQueued(string $attemptId): void;

    /** @return array{id:string,provider:string,need_kind:string,need_key:string}|null */
    public function beginGate(string $attemptId): ?array;

    public function markReady(string $attemptId): void;

    /** @return array{id:string,entity_type:string,entity_id:string,provider:string,need_kind:string,need_key:string,reason:string}|null */
    public function beginExecution(string $attemptId): ?array;

    /** @param array<string, mixed> $payload */
    public function markSucceeded(string $attemptId, array $payload = []): void;

    /** @param array<string, mixed> $payload */
    public function markReviewRequired(string $attemptId, string $message, array $payload = []): void;

    /** @param array<string, mixed> $payload */
    public function markRejected(string $attemptId, string $message, array $payload = []): void;

    public function markDeferred(string $attemptId, string $message): void;

    public function markFailed(string $attemptId, string $message): void;
}

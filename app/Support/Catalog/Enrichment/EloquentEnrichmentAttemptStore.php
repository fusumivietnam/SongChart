<?php

declare(strict_types=1);

namespace App\Support\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Domain\Catalog\Enrichment\EnrichmentDispatch;
use App\Models\Catalog\EnrichmentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentEnrichmentAttemptStore implements EnrichmentAttemptStore
{
    public function reserve(EnrichmentDispatch $dispatch): string
    {
        $id = (string) Str::ulid();
        $now = now();

        EnrichmentAttempt::query()->insertOrIgnore([
            'id' => $id,
            'entity_type' => $dispatch->entityType,
            'entity_id' => $dispatch->entityId,
            'need_kind' => $dispatch->need->kind,
            'need_key' => $dispatch->need->key,
            'provider' => $dispatch->need->provider,
            'priority' => $dispatch->need->priority,
            'cost_class' => $dispatch->need->costClass,
            'reason' => $dispatch->need->reason,
            'idempotency_key' => $dispatch->idempotencyKey,
            'status' => 'planned',
            'attempt_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (string) EnrichmentAttempt::query()
            ->where('idempotency_key', $dispatch->idempotencyKey)
            ->firstOrFail()
            ->getKey();
    }

    public function markQueued(string $attemptId): void
    {
        EnrichmentAttempt::query()
            ->whereKey($attemptId)
            ->whereIn('status', ['planned', 'deferred'])
            ->update(['status' => 'queued', 'last_error' => null, 'updated_at' => now()]);
    }

    public function beginGate(string $attemptId): ?array
    {
        $updated = EnrichmentAttempt::query()
            ->whereKey($attemptId)
            ->whereIn('status', ['queued', 'deferred'])
            ->update([
                'status' => 'gating',
                'attempt_count' => DB::raw('attempt_count + 1'),
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            return null;
        }

        $attempt = EnrichmentAttempt::query()->findOrFail($attemptId);

        return [
            'id' => (string) $attempt->getKey(),
            'provider' => (string) $attempt->provider,
            'need_kind' => (string) $attempt->need_kind,
            'need_key' => (string) $attempt->need_key,
        ];
    }

    public function markReady(string $attemptId): void
    {
        $this->transition($attemptId, 'ready', null);
    }

    public function beginExecution(string $attemptId): ?array
    {
        $updated = EnrichmentAttempt::query()
            ->whereKey($attemptId)
            ->where('status', 'ready')
            ->update(['status' => 'executing', 'updated_at' => now()]);

        if ($updated !== 1) {
            return null;
        }

        $attempt = EnrichmentAttempt::query()->findOrFail($attemptId);

        return [
            'id' => (string) $attempt->getKey(),
            'entity_type' => (string) $attempt->entity_type,
            'entity_id' => (string) $attempt->entity_id,
            'provider' => (string) $attempt->provider,
            'need_kind' => (string) $attempt->need_kind,
            'need_key' => (string) $attempt->need_key,
            'reason' => (string) $attempt->reason,
        ];
    }

    public function markSucceeded(string $attemptId, array $payload = []): void
    {
        EnrichmentAttempt::query()->whereKey($attemptId)->update([
            'status' => 'succeeded',
            'last_error' => null,
            'review_reason' => null,
            'result_payload' => $payload === [] ? null : json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'completed_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markReviewRequired(string $attemptId, string $message, array $payload = []): void
    {
        EnrichmentAttempt::query()->whereKey($attemptId)->update([
            'status' => 'review_required',
            'last_error' => null,
            'review_reason' => $message,
            'result_payload' => $payload === [] ? null : json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'completed_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markRejected(string $attemptId, string $message, array $payload = []): void
    {
        EnrichmentAttempt::query()->whereKey($attemptId)->update([
            'status' => 'rejected',
            'last_error' => null,
            'review_reason' => $message,
            'result_payload' => $payload === [] ? null : json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'completed_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markDeferred(string $attemptId, string $message): void
    {
        $this->transition($attemptId, 'deferred', $message);
    }

    public function markFailed(string $attemptId, string $message): void
    {
        $this->transition($attemptId, 'failed', $message);
    }

    private function transition(string $attemptId, string $status, ?string $message): void
    {
        EnrichmentAttempt::query()->whereKey($attemptId)->update([
            'status' => $status,
            'last_error' => $message,
            'completed_at' => $status === 'failed' ? now() : null,
            'updated_at' => now(),
        ]);
    }
}

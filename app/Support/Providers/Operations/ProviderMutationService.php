<?php

declare(strict_types=1);

namespace App\Support\Providers\Operations;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Models\Providers\ProviderOperationAudit;
use App\Models\User;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use LogicException;

final readonly class ProviderMutationService
{
    public function __construct(
        private ProviderImportOrchestrator $orchestrator,
        private PrivilegedAuditLogger $privilegedAudit,
    ) {}

    public function mutateProvider(Provider $provider, string $action, User $actor, string $rationale, string $idempotencyKey): void
    {
        DB::transaction(function () use ($provider, $action, $actor, $rationale, $idempotencyKey): void {
            /** @var Provider $locked */
            $locked = Provider::query()->lockForUpdate()->findOrFail($provider->getKey());
            if ($this->alreadyApplied($idempotencyKey, $action, (string) $locked->getKey(), null)) {
                return;
            }

            $before = $this->providerState($locked);
            match ($action) {
                'enable' => $this->enable($locked),
                'disable' => $locked->forceFill(['is_enabled' => false])->save(),
                'retire' => $this->retire($locked),
                default => throw new LogicException('Unsupported provider mutation action.'),
            };
            $locked->refresh();
            $after = $this->providerState($locked);
            $this->audit($locked, null, $actor, $action, $idempotencyKey, $before, $after, $rationale);
            $this->privilegedAudit->record(
                event: 'provider.'.$action,
                description: 'Provider '.$action.' operation completed.',
                subject: $locked,
                actor: $actor,
                before: $before,
                after: $after,
                context: ['idempotency_key' => $idempotencyKey],
                rationale: $rationale,
            );
        });
    }

    public function recoverImport(ProviderImportRun $run, string $action, User $actor, string $rationale, string $idempotencyKey): void
    {
        DB::transaction(function () use ($run, $action, $actor, $rationale, $idempotencyKey): void {
            /** @var ProviderImportRun $locked */
            $locked = ProviderImportRun::query()->lockForUpdate()->findOrFail($run->getKey());
            if ($this->alreadyApplied($idempotencyKey, $action, null, (string) $locked->getKey())) {
                return;
            }

            $provider = $locked->provider()->firstOrFail();
            $providerStatus = ProviderStatus::from((string) $provider->getRawOriginal('status'));
            if (! $provider->is_enabled || $providerStatus === ProviderStatus::Retired) {
                throw new LogicException('Disabled or retired providers cannot dispatch import recovery work.');
            }

            $before = $this->runState($locked);
            match ($action) {
                'resume' => $this->orchestrator->resume($locked),
                'cancel' => $this->cancel($locked),
                'retry' => $this->retry($locked),
                default => throw new LogicException('Unsupported provider import recovery action.'),
            };
            $locked->refresh();
            $after = $this->runState($locked);
            $this->audit($provider, $locked, $actor, $action, $idempotencyKey, $before, $after, $rationale);
            $this->privilegedAudit->record(
                event: 'provider-import.'.$action,
                description: 'Provider import '.$action.' operation completed.',
                subject: $locked,
                actor: $actor,
                before: $before,
                after: $after,
                context: [
                    'provider_id' => (string) $provider->getKey(),
                    'idempotency_key' => $idempotencyKey,
                ],
                rationale: $rationale,
            );
        });
    }

    private function enable(Provider $provider): bool
    {
        if (ProviderStatus::from((string) $provider->getRawOriginal('status')) === ProviderStatus::Retired) {
            throw new LogicException('Retired providers cannot be re-enabled.');
        }

        return $provider->forceFill(['is_enabled' => true])->save();
    }

    private function retire(Provider $provider): bool
    {
        $active = ProviderImportRun::query()
            ->where('provider_id', $provider->getKey())
            ->whereIn('status', [
                ProviderImportRunStatus::Queued->value,
                ProviderImportRunStatus::Running->value,
                ProviderImportRunStatus::Paused->value,
                ProviderImportRunStatus::Retrying->value,
            ])
            ->exists();
        if ($active) {
            throw new LogicException('Providers with active import runs cannot be retired.');
        }

        return $provider->forceFill(['is_enabled' => false, 'status' => ProviderStatus::Retired])->save();
    }

    private function cancel(ProviderImportRun $run): void
    {
        if ($this->orchestrator->status($run)->isTerminal()) {
            throw new LogicException('Terminal provider import runs cannot be cancelled.');
        }
        $this->orchestrator->requestCancellation($run);
    }

    private function retry(ProviderImportRun $run): void
    {
        $status = $this->orchestrator->status($run);
        if (! in_array($status, [ProviderImportRunStatus::Failed, ProviderImportRunStatus::CompletedWithErrors], true)) {
            throw new LogicException('Only failed or completed-with-errors imports can be retried.');
        }

        $run->forceFill([
            'status' => ProviderImportRunStatus::Queued,
            'finished_at' => null,
            'error_summary' => null,
            'cancellation_requested_at' => null,
            'resume_after' => null,
            'attempts' => $run->attempts + 1,
        ])->save();
        $this->orchestrator->resume($run);
    }

    private function alreadyApplied(string $key, string $action, ?string $providerId, ?string $runId): bool
    {
        $audit = ProviderOperationAudit::query()->where('idempotency_key', $key)->first();
        if ($audit === null) {
            return false;
        }
        if ($audit->action !== $action || (string) $audit->provider_id !== (string) $providerId || (string) $audit->provider_import_run_id !== (string) $runId) {
            throw new LogicException('Idempotency key has already been used for a different operation.');
        }

        return true;
    }

    /** @return array<string, mixed> */
    private function providerState(Provider $provider): array
    {
        $status = ProviderStatus::from((string) $provider->getRawOriginal('status'));

        return ['status' => $status->value, 'is_enabled' => (bool) $provider->is_enabled];
    }

    /** @return array<string, mixed> */
    private function runState(ProviderImportRun $run): array
    {
        $status = ProviderImportRunStatus::from((string) $run->getRawOriginal('status'));
        $cancellationRequestedAt = $run->getAttribute('cancellation_requested_at');

        return [
            'status' => $status->value,
            'attempts' => (int) $run->attempts,
            'cancellation_requested_at' => $cancellationRequestedAt instanceof DateTimeInterface
                ? $cancellationRequestedAt->format(DATE_ATOM)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function audit(?Provider $provider, ?ProviderImportRun $run, User $actor, string $action, string $key, array $before, array $after, string $rationale): void
    {
        ProviderOperationAudit::query()->create([
            'provider_id' => $provider?->getKey(),
            'provider_import_run_id' => $run?->getKey(),
            'actor_user_id' => $actor->getKey(),
            'action' => $action,
            'idempotency_key' => $key,
            'before_state' => $before,
            'after_state' => $after,
            'rationale' => $rationale,
            'occurred_at' => now(),
        ]);
    }
}

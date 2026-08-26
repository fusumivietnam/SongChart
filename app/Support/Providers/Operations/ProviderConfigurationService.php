<?php

declare(strict_types=1);

namespace App\Support\Providers\Operations;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Models\Provider;
use App\Models\Providers\ProviderOperationAudit;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use LogicException;

final readonly class ProviderConfigurationService
{
    public function __construct(private PrivilegedAuditLogger $privilegedAudit) {}

    /**
     * @param array<string, scalar|null> $settings
     * @param array<string, string|null> $secrets
     */
    public function apply(
        Provider $provider,
        array $settings,
        array $secrets,
        User $actor,
        string $rationale,
        string $idempotencyKey,
    ): void {
        DB::transaction(function () use ($provider, $settings, $secrets, $actor, $rationale, $idempotencyKey): void {
            /** @var Provider $locked */
            $locked = Provider::query()->lockForUpdate()->findOrFail($provider->getKey());
            $existingAudit = ProviderOperationAudit::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existingAudit !== null) {
                if ($existingAudit->action !== 'configure' || (string) $existingAudit->provider_id !== (string) $locked->getKey()) {
                    throw new LogicException('Idempotency key has already been used for a different operation.');
                }

                return;
            }

            $configuration = $locked->configuration ?? [];
            $before = $this->safeState($configuration);

            foreach ($settings as $key => $value) {
                if ($value === null || $value === '') {
                    unset($configuration[$key]);
                } else {
                    $configuration[$key] = $value;
                }
            }

            $secretsState = $configuration['_secrets'] ?? null;
            $encryptedSecrets = is_array($secretsState) ? $secretsState : [];
            foreach ($secrets as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $encryptedSecrets[$key] = Crypt::encryptString($value);
            }
            if ($encryptedSecrets !== []) {
                $configuration['_secrets'] = $encryptedSecrets;
            }

            $locked->forceFill(['configuration' => $configuration])->save();
            $after = $this->safeState($configuration);

            ProviderOperationAudit::query()->create([
                'provider_id' => $locked->getKey(),
                'provider_import_run_id' => null,
                'actor_user_id' => $actor->getKey(),
                'action' => 'configure',
                'idempotency_key' => $idempotencyKey,
                'before_state' => $before,
                'after_state' => $after,
                'rationale' => $rationale,
                'occurred_at' => now(),
            ]);

            $this->privilegedAudit->record(
                event: 'provider.configure',
                description: 'Provider runtime configuration updated.',
                subject: $locked,
                actor: $actor,
                before: $before,
                after: $after,
                context: ['idempotency_key' => $idempotencyKey],
                rationale: $rationale,
            );
        });
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    private function safeState(array $configuration): array
    {
        $secretsState = $configuration['_secrets'] ?? null;
        $secrets = is_array($secretsState) ? $secretsState : [];
        unset($configuration['_secrets']);

        return [
            'settings' => $configuration,
            'configured_secret_keys' => array_values(array_filter(array_keys($secrets), 'is_string')),
        ];
    }
}

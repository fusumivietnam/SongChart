<?php

declare(strict_types=1);

namespace App\Support\Providers\Operations;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Provider;
use App\Models\Providers\ProviderCredential;
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
     * @param array<string, list<string>> $credentialPools
     */
    public function apply(
        Provider $provider,
        array $settings,
        array $secrets,
        array $credentialPools,
        bool $enabled,
        User $actor,
        string $rationale,
        string $idempotencyKey,
    ): void {
        DB::transaction(function () use ($provider, $settings, $secrets, $credentialPools, $enabled, $actor, $rationale, $idempotencyKey): void {
            /** @var Provider $locked */
            $locked = Provider::query()->lockForUpdate()->findOrFail($provider->getKey());
            $existingAudit = ProviderOperationAudit::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existingAudit !== null) {
                if ($existingAudit->action !== 'configure' || (string) $existingAudit->provider_id !== (string) $locked->getKey()) {
                    throw new LogicException('Idempotency key has already been used for a different operation.');
                }
                return;
            }
            if ($enabled && ProviderStatus::from((string) $locked->getRawOriginal('status')) === ProviderStatus::Retired) {
                throw new LogicException('Retired providers cannot be enabled from system settings.');
            }

            $configuration = $locked->configuration ?? [];
            $before = $this->safeState($locked, $configuration);
            foreach ($settings as $key => $value) {
                if ($value === null || $value === '') { unset($configuration[$key]); } else { $configuration[$key] = $value; }
            }
            $secretsState = $configuration['_secrets'] ?? null;
            $encryptedSecrets = is_array($secretsState) ? $secretsState : [];
            foreach ($secrets as $key => $value) {
                if ($value === null || $value === '') { continue; }
                $encryptedSecrets[$key] = Crypt::encryptString($value);
            }
            if ($encryptedSecrets !== []) { $configuration['_secrets'] = $encryptedSecrets; }

            foreach ($credentialPools as $kind => $values) {
                if ($values === []) { continue; }
                ProviderCredential::query()->where('provider_id', $locked->getKey())->where('kind', $kind)->delete();
                foreach (array_values(array_unique($values)) as $index => $value) {
                    ProviderCredential::query()->create([
                        'provider_id' => $locked->getKey(),
                        'kind' => $kind,
                        'label' => strtoupper(str_replace('_', ' ', $kind)).' '.($index + 1),
                        'encrypted_secret' => Crypt::encryptString($value),
                        'is_enabled' => true,
                        'priority' => 100,
                    ]);
                }
            }

            $locked->forceFill(['configuration' => $configuration, 'is_enabled' => $enabled])->save();
            $after = $this->safeState($locked->refresh(), $configuration);
            ProviderOperationAudit::query()->create([
                'provider_id' => $locked->getKey(), 'provider_import_run_id' => null, 'actor_user_id' => $actor->getKey(),
                'action' => 'configure', 'idempotency_key' => $idempotencyKey, 'before_state' => $before, 'after_state' => $after,
                'rationale' => $rationale, 'occurred_at' => now(),
            ]);
            $this->privilegedAudit->record(
                event: 'provider.configure', description: 'Provider runtime configuration and operational state updated.',
                subject: $locked, actor: $actor, before: $before, after: $after,
                context: ['idempotency_key' => $idempotencyKey], rationale: $rationale,
            );
        });
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array{
     *     settings: array<string, mixed>,
     *     configured_secret_keys: list<string>,
     *     credential_pool_counts: array<string, int>,
     *     is_enabled: bool
     * }
     */
    private function safeState(Provider $provider, array $configuration): array
    {
        $secretsState = $configuration['_secrets'] ?? null;
        $secrets = is_array($secretsState) ? $secretsState : [];
        unset($configuration['_secrets']);
        $credentialCounts = ProviderCredential::query()->where('provider_id', $provider->getKey())
            ->selectRaw('kind, count(*) as aggregate')->groupBy('kind')->pluck('aggregate', 'kind')->map(fn ($v) => (int) $v)->all();
        return [
            'settings' => $configuration,
            'configured_secret_keys' => array_values(array_filter(array_keys($secrets), 'is_string')),
            'credential_pool_counts' => $credentialCounts,
            'is_enabled' => (bool) $provider->is_enabled,
        ];
    }
}

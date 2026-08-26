<?php

declare(strict_types=1);

namespace App\Actions\Providers\Ingestion;

use App\Domain\Providers\Ingestion\DTO\ProviderImportPlan;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use RuntimeException;

final class StartGovernedProviderImportPlan
{
    public function __construct(private readonly ProviderImportOrchestrator $imports) {}

    public function execute(ProviderImportPlan $plan): ProviderImportRun
    {
        if (! $plan->executable) {
            throw new RuntimeException('Import plan is not executable because preview validation requires review.');
        }

        $provider = Provider::query()->where('slug', $plan->providerSlug)->first();
        if ($provider === null) {
            throw new RuntimeException('The selected data source is not registered.');
        }
        if (! $provider->is_enabled) {
            throw new RuntimeException('The selected data source is disabled. Enable it before importing.');
        }

        return $this->imports->start(
            provider: $provider,
            entityType: $plan->entityType,
            operation: $plan->operation,
            externalId: $plan->externalId,
            pageSize: 1,
            options: [
                'source' => 'admin-import-plan',
                'plan_fingerprint' => $plan->fingerprint,
            ],
        );
    }
}

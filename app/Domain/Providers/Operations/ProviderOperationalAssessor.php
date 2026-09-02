<?php

declare(strict_types=1);

namespace App\Domain\Providers\Operations;

use App\Domain\Providers\Enums\ProviderOperationalState;
use App\Domain\Providers\Enums\ProviderRuntimeIssueCode;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\ProviderTaxonomy;
use App\Models\Provider;

final class ProviderOperationalAssessor
{
    public function assess(Provider $provider, bool $credentialConfigured, ?bool $healthy): ProviderOperationalAssessment
    {
        $issues = [];
        $slug = (string) $provider->getAttribute('slug');
        $status = $provider->status;

        if (ProviderTaxonomy::definition($slug) === null) {
            $issues[] = ProviderRuntimeIssueCode::TaxonomyUnregistered;
        }

        if (! $provider->is_enabled) {
            $issues[] = ProviderRuntimeIssueCode::Disabled;

            return new ProviderOperationalAssessment(ProviderOperationalState::Disabled, $issues);
        }

        if (! in_array($status, [ProviderStatus::Approved, ProviderStatus::Degraded], true)) {
            $issues[] = ProviderRuntimeIssueCode::Unapproved;

            return new ProviderOperationalAssessment(ProviderOperationalState::Unapproved, $issues);
        }

        if (! $credentialConfigured || ProviderTaxonomy::definition($slug) === null) {
            if (! $credentialConfigured) {
                $issues[] = ProviderRuntimeIssueCode::CredentialMissing;
            }

            return new ProviderOperationalAssessment(ProviderOperationalState::Misconfigured, $issues);
        }

        if ($healthy === null) {
            $issues[] = ProviderRuntimeIssueCode::HealthUnknown;

            return new ProviderOperationalAssessment(ProviderOperationalState::Degraded, $issues);
        }

        if ($healthy === false) {
            $issues[] = ProviderRuntimeIssueCode::RuntimeUnhealthy;

            return new ProviderOperationalAssessment(ProviderOperationalState::Degraded, $issues);
        }

        if ($status === ProviderStatus::Degraded) {
            $issues[] = ProviderRuntimeIssueCode::ProviderDegraded;

            return new ProviderOperationalAssessment(ProviderOperationalState::Degraded, $issues);
        }

        return new ProviderOperationalAssessment(ProviderOperationalState::Ready, []);
    }
}

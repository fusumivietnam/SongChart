<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderRuntimeIssueCode: string
{
    case TaxonomyUnregistered = 'provider.taxonomy_unregistered';
    case Disabled = 'provider.disabled';
    case Unapproved = 'provider.unapproved';
    case CredentialMissing = 'provider.credential_missing';
    case HealthUnknown = 'provider.health_unknown';
    case RuntimeUnhealthy = 'provider.runtime_unhealthy';
    case ProviderDegraded = 'provider.degraded';
}

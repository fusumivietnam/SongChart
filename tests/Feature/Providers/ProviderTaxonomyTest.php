<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderCapabilityCode;
use App\Domain\Providers\Enums\ProviderCategory;
use App\Domain\Providers\Enums\ProviderOperationalState;
use App\Domain\Providers\Enums\ProviderRole;
use App\Domain\Providers\Enums\ProviderRuntimeIssueCode;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Operations\ProviderOperationalAssessor;
use App\Domain\Providers\ProviderTaxonomy;
use App\Models\Provider;
use App\Models\ProviderCapability;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('classifies known providers by stable category role and capability codes', function (): void {
    $musicBrainz = ProviderTaxonomy::definition('musicbrainz');
    $youtube = ProviderTaxonomy::definition('youtube');
    $sentry = ProviderTaxonomy::definition('sentry');

    expect($musicBrainz)->not->toBeNull()
        ->and($musicBrainz['category'])->toBe(ProviderCategory::Music)
        ->and($musicBrainz['role'])->toBe(ProviderRole::Data)
        ->and($musicBrainz['capabilities'])->toContain(ProviderCapabilityCode::CatalogImport)
        ->and($youtube)->not->toBeNull()
        ->and($youtube['role'])->toBe(ProviderRole::Destination)
        ->and($youtube['capabilities'])->toContain(ProviderCapabilityCode::MediaInspect)
        ->and($sentry)->not->toBeNull()
        ->and($sentry['role'])->toBe(ProviderRole::Service)
        ->and($sentry['capabilities'])->toContain(ProviderCapabilityCode::ObservabilityExceptions);
});

it('rejects provider category drift at the persistence boundary', function (): void {
    Provider::query()->create([
        'slug' => 'invalid-category-provider',
        'name' => 'Invalid Category Provider',
        'category' => 'analytic',
        'status' => ProviderStatus::Research,
        'is_enabled' => false,
    ]);
})->throws(InvalidArgumentException::class, 'Unsupported provider category [analytic].');

it('rejects provider capability drift at the persistence boundary', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'taxonomy-test-provider',
        'name' => 'Taxonomy Test Provider',
        'category' => ProviderCategory::Utility->value,
        'status' => ProviderStatus::Research,
        'is_enabled' => false,
    ]);

    ProviderCapability::query()->create([
        'provider_id' => $provider->getKey(),
        'capability' => 'analytics.event',
        'status' => 'supported',
    ]);
})->throws(InvalidArgumentException::class, 'Unsupported provider capability [analytics.event].');

it('fails closed when operational provider evidence is incomplete', function (): void {
    $provider = new Provider([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => ProviderCategory::Music->value,
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);

    $assessor = new ProviderOperationalAssessor;

    $missingCredential = $assessor->assess($provider, false, true);
    $unknownHealth = $assessor->assess($provider, true, null);
    $ready = $assessor->assess($provider, true, true);

    expect($missingCredential->state)->toBe(ProviderOperationalState::Misconfigured)
        ->and($missingCredential->issues)->toContain(ProviderRuntimeIssueCode::CredentialMissing)
        ->and($unknownHealth->state)->toBe(ProviderOperationalState::Degraded)
        ->and($unknownHealth->issues)->toContain(ProviderRuntimeIssueCode::HealthUnknown)
        ->and($ready->state)->toBe(ProviderOperationalState::Ready)
        ->and($ready->issueCodes())->toBe([]);
});

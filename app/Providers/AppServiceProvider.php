<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Analytics\ProductAnalytics;
use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Contracts\Catalog\EnrichmentEvidenceAdmissionPolicy;
use App\Contracts\Catalog\EnrichmentExecutor;
use App\Contracts\Catalog\EnrichmentPlanner;
use App\Contracts\Catalog\EntityIdentityBridge;
use App\Contracts\Catalog\FieldAuthorityPolicy;
use App\Contracts\HumanVerification\HumanVerification;
use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Services\Analytics\NullProductAnalytics;
use App\Services\HumanVerification\NullHumanVerification;
use App\Support\Audit\SpatiePrivilegedAuditLogger;
use App\Support\Catalog\Enrichment\ConfigEnrichmentPlanner;
use App\Support\Catalog\Enrichment\EloquentEnrichmentAttemptStore;
use App\Support\Catalog\Enrichment\EloquentEntityIdentityBridge;
use App\Support\Catalog\Enrichment\GovernedEnrichmentEvidenceAdmissionPolicy;
use App\Support\Catalog\Enrichment\GovernedEnrichmentExecutor;
use App\Support\Catalog\Fusion\CanonicalFieldResolver;
use App\Support\Catalog\Fusion\ConfigFieldAuthorityPolicy;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;
use App\Support\Providers\Normalization\MusicBrainzProviderMapper;
use App\Support\Providers\Normalization\ProviderSpecificMapperRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductAnalytics::class, NullProductAnalytics::class);
        $this->app->singleton(HumanVerification::class, NullHumanVerification::class);
        $this->app->singleton(PrivilegedAuditLogger::class, SpatiePrivilegedAuditLogger::class);
        $this->app->singleton(FieldAuthorityPolicy::class, fn (): FieldAuthorityPolicy => new ConfigFieldAuthorityPolicy((array) config('data-fusion')));
        $this->app->singleton(EntityIdentityBridge::class, EloquentEntityIdentityBridge::class);
        $this->app->singleton(EnrichmentAttemptStore::class, EloquentEnrichmentAttemptStore::class);
        $this->app->singleton(EnrichmentExecutor::class, GovernedEnrichmentExecutor::class);
        $this->app->singleton(EnrichmentEvidenceAdmissionPolicy::class, GovernedEnrichmentEvidenceAdmissionPolicy::class);
        $this->app->singleton(NormalizedProviderEntityValidator::class, DefaultNormalizedProviderEntityValidator::class);
        $this->app->singleton(
            ProviderSpecificMapperRegistry::class,
            static fn (): ProviderSpecificMapperRegistry => new ProviderSpecificMapperRegistry([
                new MusicBrainzProviderMapper,
            ]),
        );
        $this->app->singleton(EnrichmentPlanner::class, fn (Application $app): EnrichmentPlanner => new ConfigEnrichmentPlanner(
            $app->make(CanonicalFieldResolver::class),
            (array) config('catalog-enrichment'),
        ));
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->environment('demo')) {
            Vite::createAssetPathsUsing(
                static fn (string $path, ?bool $secure = null): string => '/'.ltrim($path, '/'),
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Contracts\Providers\Destinations\VideoDestinationDiscovery;
use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Providers\Identity\Contracts\ExactIdentityResolver;
use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationPipeline;
use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Support\Providers\Catalog\InMemoryProviderCatalogAdapterRegistry;
use App\Support\Providers\Catalog\MusicBrainzProviderCatalogAdapter;
use App\Support\Providers\Destinations\YouTubeVideoDestinationDiscovery;
use App\Support\Providers\Identity\EloquentExactIdentityResolver;
use App\Support\Providers\Identity\Review\EloquentIdentityConflictReviewService;
use App\Support\Providers\Mutation\DefaultCanonicalMutationPipeline;
use App\Support\Providers\Mutation\EloquentCanonicalMutationAction;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;
use App\Support\Providers\ProviderAdapterRegistry;
use App\Support\Providers\Rate\CacheProviderRequestGate;
use App\Support\Providers\Rate\ConfigProviderRatePolicyRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class ProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ProviderAdapterRegistry::class,
            static fn (Application $app): ProviderAdapterRegistry => new ProviderAdapterRegistry(
                $app->tagged('songchart.provider-adapters'),
            ),
        );

        $this->app->singleton(
            NormalizedProviderEntityValidator::class,
            DefaultNormalizedProviderEntityValidator::class,
        );

        $this->app->bind(IdentityConflictReviewService::class, EloquentIdentityConflictReviewService::class);
        $this->app->bind(ExactIdentityResolver::class, EloquentExactIdentityResolver::class);

        $this->app->tag([EloquentCanonicalMutationAction::class], 'songchart.canonical-mutation-actions');
        $this->app->singleton(
            CanonicalMutationPipeline::class,
            static fn (Application $app): CanonicalMutationPipeline => new DefaultCanonicalMutationPipeline(
                $app->tagged('songchart.canonical-mutation-actions'),
            ),
        );

        $this->app->singleton(ProviderRatePolicyRegistry::class, ConfigProviderRatePolicyRegistry::class);
        $this->app->singleton(ProviderRequestGate::class, CacheProviderRequestGate::class);
        $this->app->singleton(VideoDestinationDiscovery::class, YouTubeVideoDestinationDiscovery::class);

        $this->app->tag([MusicBrainzProviderCatalogAdapter::class], 'songchart.provider-catalog-adapters');

        $this->app->singleton(
            ProviderCatalogAdapterRegistry::class,
            static fn (Application $app): ProviderCatalogAdapterRegistry => new InMemoryProviderCatalogAdapterRegistry(
                $app->tagged('songchart.provider-catalog-adapters'),
            ),
        );
    }
}

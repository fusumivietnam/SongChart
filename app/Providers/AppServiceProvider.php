<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Discovery\Listeners\RebuildDiscoveryAfterCanonicalChange;
use App\Contracts\Analytics\ProductAnalytics;
use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Contracts\Catalog\EnrichmentEvidenceAdmissionPolicy;
use App\Contracts\Catalog\EnrichmentExecutor;
use App\Contracts\Catalog\EnrichmentPlanner;
use App\Contracts\Catalog\EntityIdentityBridge;
use App\Contracts\Catalog\FieldAuthorityPolicy;
use App\Contracts\HumanVerification\HumanVerification;
use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Catalog\Events\CanonicalEntityChanged;
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
use App\Support\Development\DevelopmentDatabaseAuthority;
use App\Support\Development\DevelopmentStorageAuthority;
use App\Support\Production\ProductionEnvironmentGuard;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;
use App\Support\Providers\Normalization\MusicBrainzProviderMapper;
use App\Support\Providers\Normalization\ProviderSpecificMapperRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

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
        Event::listen(CanonicalEntityChanged::class, RebuildDiscoveryAfterCanonicalChange::class);

        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->environment('local')) {
            DevelopmentDatabaseAuthority::assertSafe(
                (array) config('songchart.development.database', []),
            );
            DevelopmentStorageAuthority::assertSafe(
                (array) config('songchart.development.storage', []),
                class_exists(AwsS3V3Adapter::class),
            );
        }

        if ((bool) config('songchart.production.environment_guard_enabled', false)) {
            ProductionEnvironmentGuard::assertSafe([
                'app_debug' => (bool) config('app.debug'),
                'app_url' => (string) config('app.url'),
                'database' => (string) config('database.default'),
                'cache' => (string) config('cache.default'),
                'queue' => (string) config('queue.default'),
                'session_secure' => config('session.secure'),
                'admin_2fa_mode' => (string) config('songchart.security.admin_2fa_mode'),
                'design_lab_enabled' => (bool) config('design-lab.enabled'),
            ]);
        }

        if ($this->app->environment('demo')) {
            $demoUrl = rtrim((string) config('app.url'), '/');
            $scheme = parse_url($demoUrl, PHP_URL_SCHEME);

            if ($demoUrl !== '' && in_array($scheme, ['http', 'https'], true)) {
                URL::forceRootUrl($demoUrl);
                URL::forceScheme($scheme);
            }

            Vite::createAssetPathsUsing(
                static fn (string $path, ?bool $secure = null): string => '/'.ltrim($path, '/'),
            );
        }
    }
}

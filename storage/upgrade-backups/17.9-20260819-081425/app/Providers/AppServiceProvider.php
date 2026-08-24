<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Analytics\ProductAnalytics;
use App\Contracts\Catalog\FieldAuthorityPolicy;
use App\Contracts\HumanVerification\HumanVerification;
use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Services\Analytics\NullProductAnalytics;
use App\Services\HumanVerification\NullHumanVerification;
use App\Support\Audit\SpatiePrivilegedAuditLogger;
use App\Support\Catalog\Fusion\ConfigFieldAuthorityPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductAnalytics::class, NullProductAnalytics::class);
        $this->app->singleton(HumanVerification::class, NullHumanVerification::class);
        $this->app->singleton(PrivilegedAuditLogger::class, SpatiePrivilegedAuditLogger::class);
        $this->app->singleton(FieldAuthorityPolicy::class, fn (): FieldAuthorityPolicy => new ConfigFieldAuthorityPolicy((array) config('data-fusion')));
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Discovery\Projections\DefaultDiscoveryProjectionPipeline;
use App\Application\Discovery\Rules\DefaultDiscoveryRuleEngine;
use App\Domain\Discovery\Contracts\DiscoveryChannelRepository;
use App\Domain\Discovery\Contracts\DiscoveryEditorialStateReader;
use App\Domain\Discovery\Contracts\DiscoveryEntityMapper;
use App\Domain\Discovery\Contracts\DiscoveryEntitySource;
use App\Domain\Discovery\Contracts\DiscoveryFieldRegistry;
use App\Domain\Discovery\Contracts\DiscoveryProjectionPipeline;
use App\Domain\Discovery\Contracts\DiscoveryProjectionReader;
use App\Domain\Discovery\Contracts\DiscoveryProjectionWriter;
use App\Domain\Discovery\Contracts\DiscoveryRuleEngine;
use App\Support\Discovery\CanonicalDiscoveryEntityMapper;
use App\Support\Discovery\CanonicalDiscoveryFieldRegistry;
use App\Support\Discovery\DatabaseDiscoveryChannelRepository;
use App\Support\Discovery\DatabaseDiscoveryEditorialStateReader;
use App\Support\Discovery\DatabaseDiscoveryProjectionStore;
use App\Support\Discovery\EloquentDiscoveryEntitySource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class DiscoveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DiscoveryFieldRegistry::class, CanonicalDiscoveryFieldRegistry::class);
        $this->app->singleton(DiscoveryEntityMapper::class, CanonicalDiscoveryEntityMapper::class);
        $this->app->singleton(DiscoveryRuleEngine::class, DefaultDiscoveryRuleEngine::class);
        $this->app->singleton(DiscoveryChannelRepository::class, DatabaseDiscoveryChannelRepository::class);
        $this->app->singleton(DiscoveryEntitySource::class, EloquentDiscoveryEntitySource::class);
        $this->app->singleton(DiscoveryEditorialStateReader::class, DatabaseDiscoveryEditorialStateReader::class);
        $this->app->singleton(DiscoveryProjectionReader::class, DatabaseDiscoveryProjectionStore::class);
        $this->app->singleton(DiscoveryProjectionWriter::class, DatabaseDiscoveryProjectionStore::class);
        $this->app->singleton(
            DiscoveryProjectionPipeline::class,
            static fn (Application $app): DiscoveryProjectionPipeline => new DefaultDiscoveryProjectionPipeline(
                $app->make(DiscoveryChannelRepository::class),
                $app->make(DiscoveryEntitySource::class),
                $app->make(DiscoveryEditorialStateReader::class),
                $app->make(DiscoveryRuleEngine::class),
                $app->make(DiscoveryProjectionWriter::class),
                max(1, (int) config('songchart.discovery.projection_ttl_minutes', 15)),
                max(1, min(1000, (int) config('songchart.discovery.projection_batch_size', DefaultDiscoveryProjectionPipeline::DEFAULT_BATCH_SIZE))),
            ),
        );
    }
}

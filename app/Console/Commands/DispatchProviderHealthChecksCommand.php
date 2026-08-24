<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Providers\DispatchProviderHealthChecks;
use Illuminate\Console\Command;

final class DispatchProviderHealthChecksCommand extends Command
{
    protected $signature = 'providers:health-check
        {--provider=* : Limit dispatch to one or more provider slugs}
        {--force : Dispatch even when a recent run is queued or running}';

    protected $description = 'Queue health checks for enabled provider adapters';

    public function handle(DispatchProviderHealthChecks $action): int
    {
        /** @var array<int, string|null> $providerValues */
        $providerValues = $this->option('provider');

        /** @var list<string> $providerSlugs */
        $providerSlugs = array_values(array_filter(
            $providerValues,
            static fn (mixed $slug): bool => is_string($slug) && $slug !== '',
        ));

        $count = $action->handle($providerSlugs, (bool) $this->option('force'));

        $this->info("Queued {$count} provider health check(s).");

        return self::SUCCESS;
    }
}

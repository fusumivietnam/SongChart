<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class ProductionRuntimeCheckCommand extends Command
{
    protected $signature = 'songchart:production-runtime-check';

    protected $description = 'Verify production application, PostgreSQL, and Redis readiness through Laravel-owned configuration.';

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->error('APP_ENV must be production.');

            return self::FAILURE;
        }

        if ((bool) config('app.debug')) {
            $this->error('APP_DEBUG must be false.');

            return self::FAILURE;
        }

        try {
            DB::connection()->select('select 1');
            $this->info('PostgreSQL OK');
        } catch (Throwable $exception) {
            $this->error('PostgreSQL connectivity failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        try {
            $pong = Redis::connection()->ping();
            if ($pong === false) {
                $this->error('Redis ping failed.');

                return self::FAILURE;
            }
            $this->info('Redis OK');
        } catch (Throwable $exception) {
            $this->error('Redis connectivity failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Production runtime readiness PASSED.');

        return self::SUCCESS;
    }
}

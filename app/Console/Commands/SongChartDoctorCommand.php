<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Engineering\RepositoryContractResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Pulse\Pulse;
use Throwable;

final class SongChartDoctorCommand extends Command
{
    protected $signature = 'songchart:doctor {--strict : Return failure when a required runtime check fails} {--contract= : Explain one executable repository authority and its registered consumers}';

    protected $description = 'Inspect SongChart runtime, package, database and queue readiness without mutating application state.';

    public function handle(): int
    {
        $contract = $this->option('contract');
        if (is_string($contract) && $contract !== '') {
            return $this->explainContract($contract);
        }

        $checks = [];

        $checks[] = $this->check('PHP', PHP_VERSION, version_compare(PHP_VERSION, '8.5.0', '>='));

        $extensions = [
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'filter',
            'hash',
            'mbstring',
            'openssl',
            'pdo',
            'pdo_pgsql',
            'session',
            'tokenizer',
            'xml',
        ];
        $missingExtensions = array_values(array_filter(
            $extensions,
            static fn (string $extension): bool => ! extension_loaded($extension),
        ));
        $checks[] = $this->check(
            'PHP extensions',
            $missingExtensions === [] ? 'required extensions loaded' : 'missing: '.implode(', ', $missingExtensions),
            $missingExtensions === [],
        );

        $laravelVersion = app()->version();
        $checks[] = $this->check('Laravel', $laravelVersion, str_starts_with($laravelVersion, '13.'));

        $driver = (string) config('database.default');
        $checks[] = $this->check('Database driver', $driver, $driver === 'pgsql');

        try {
            $databaseVersion = (string) DB::scalar('select version()');
            $versionNum = (int) DB::scalar("select current_setting('server_version_num')");
            $databaseMajor = intdiv($versionNum, 10000);
            $checks[] = $this->check(
                'PostgreSQL',
                $databaseVersion,
                $driver === 'pgsql' && $databaseMajor === 18,
            );
        } catch (Throwable $exception) {
            $checks[] = $this->check('PostgreSQL', $exception->getMessage(), false);
        }

        $queue = (string) config('queue.default');
        $checks[] = $this->check('Queue connection', $queue, $queue === 'redis');

        try {
            $pong = Redis::connection()->ping();
            $checks[] = $this->check('Redis', is_scalar($pong) ? (string) $pong : 'reachable', true);
        } catch (Throwable $exception) {
            $checks[] = $this->check('Redis', $exception->getMessage(), false);
        }

        $pulseInstalled = class_exists(Pulse::class);
        $checks[] = $this->check('Laravel Pulse', $pulseInstalled ? 'installed' : 'missing', $pulseInstalled);

        $horizonInstalled = class_exists('Laravel\\Horizon\\Horizon');
        $horizonRequired = PHP_OS_FAMILY !== 'Windows';
        $checks[] = $this->check(
            'Laravel Horizon',
            $horizonInstalled ? 'installed' : ($horizonRequired ? 'optional/not installed' : 'skipped on native Windows'),
            true,
        );

        $lockPath = base_path('composer.lock');
        $checks[] = $this->check('Composer lock', is_file($lockPath) ? 'present' : 'missing', is_file($lockPath));

        $failed = array_filter($checks, static fn (array $check): bool => ! $check['passed']);

        $this->newLine();
        $this->table(
            ['Check', 'Value', 'Status'],
            array_map(
                static fn (array $check): array => [$check['name'], $check['value'], $check['passed'] ? 'PASS' : 'FAIL'],
                $checks,
            ),
        );

        if ($failed === []) {
            $this->info('SongChart environment is READY.');

            return self::SUCCESS;
        }

        $this->warn(sprintf('SongChart environment has %d failed required check(s).', count($failed)));

        return $this->option('strict') ? self::FAILURE : self::SUCCESS;
    }

    private function explainContract(string $name): int
    {
        try {
            $resolver = new RepositoryContractResolver(base_path());
            $authority = $resolver->authority($name);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $this->line('Available authorities:');
            try {
                $resolver ??= new RepositoryContractResolver(base_path());
                foreach ($resolver->authorityNames() as $authorityName) {
                    $this->line("- {$authorityName}");
                }
            } catch (Throwable) {
                // The original resolver error is already sufficient.
            }

            return self::FAILURE;
        }

        $this->info("Repository authority: {$name}");
        $this->table(
            ['Property', 'Value'],
            [
                ['Source', (string) $authority['source']],
                ['Fingerprint', (string) $authority['fingerprint']],
                ['Consumers', (string) count($authority['consumers'])],
            ],
        );

        if ($authority['consumers'] !== []) {
            $this->line('Registered consumers:');
            foreach ($authority['consumers'] as $consumer) {
                $this->line("- {$consumer}");
            }
        }

        $violations = array_values(array_filter(
            $resolver->staleConsumers(),
            static fn (array $violation): bool => $violation['authority'] === $name,
        ));

        if ($violations === []) {
            $this->info('Authority consumers are resolved without forbidden raw literals.');

            return self::SUCCESS;
        }

        $this->error('Stale/raw authority consumers detected:');
        foreach ($violations as $violation) {
            $this->line('- '.$violation['consumer'].': '.$violation['reason']);
        }

        return self::FAILURE;
    }

    /**
     * @return array{name: string, value: string, passed: bool}
     */
    private function check(string $name, string $value, bool $passed): array
    {
        return compact('name', 'value', 'passed');
    }
}

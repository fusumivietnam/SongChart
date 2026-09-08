<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Engineering\RepositoryContractResolver;
use Composer\Semver\Semver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Pulse\Pulse;
use Throwable;

final class SongChartDoctorCommand extends Command
{
    protected $signature = 'songchart:doctor
        {--strict : Return failure when a required runtime check fails}
        {--contract= : Explain one executable repository authority and its registered consumers}
        {--profile=auto : Show the requested or automatically recommended light/standard/full execution profile}';

    protected $description = 'Inspect SongChart runtime, package, database, queue and execution-profile readiness without mutating application state.';

    public function handle(): int
    {
        $contract = $this->option('contract');
        if (is_string($contract) && $contract !== '') {
            return $this->explainContract($contract);
        }

        $stack = $this->readJson(base_path('docs/project/stack/stack-manifest.json'));
        $composer = $this->readJson(base_path('composer.json'));
        $resilience = $this->readJson(base_path('docs/project/governance/resilience-matrix.json'));
        $phpConstraint = (string) ($composer['require']['php'] ?? '');
        $laravelConstraint = (string) ($composer['require']['laravel/framework'] ?? '');
        $postgresMajorTarget = $stack['policies']['release_database_major'] ?? null;
        $nodeMajorTarget = $stack['policies']['node_runtime_major'] ?? null;

        $checks = [];
        $checks[] = $this->check(
            'PHP',
            PHP_VERSION.' (authority '.$phpConstraint.')',
            $phpConstraint !== '' && Semver::satisfies(PHP_VERSION, $phpConstraint),
        );

        $extensions = [
            'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl',
            'pdo', 'pdo_pgsql', 'session', 'tokenizer', 'xml',
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
        $checks[] = $this->check(
            'Laravel',
            $laravelVersion.' (authority '.$laravelConstraint.')',
            $laravelConstraint !== '' && Semver::satisfies($laravelVersion, $laravelConstraint),
        );

        $nodeVersion = trim((string) shell_exec('node --version 2>/dev/null'));
        $nodeMajor = preg_match('/^v?(\d+)/', $nodeVersion, $nodeMatch) === 1 ? (int) $nodeMatch[1] : null;
        $checks[] = $this->check(
            'Node build runtime',
            $nodeVersion !== '' ? $nodeVersion.' (target '.(string) $nodeMajorTarget.')' : 'not available in this runtime',
            $nodeVersion === '' || (is_int($nodeMajorTarget) && $nodeMajor === $nodeMajorTarget),
        );

        $driver = (string) config('database.default');
        $checks[] = $this->check('Database driver', $driver, $driver === 'pgsql');

        try {
            $databaseVersion = (string) DB::scalar('select version()');
            $versionNum = (int) DB::scalar("select current_setting('server_version_num')");
            $databaseMajor = intdiv($versionNum, 10000);
            $checks[] = $this->check(
                'PostgreSQL',
                $databaseVersion.' (target '.(string) $postgresMajorTarget.')',
                $driver === 'pgsql' && is_int($postgresMajorTarget) && $databaseMajor === $postgresMajorTarget,
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

        $hardware = $this->hardwareProfile($resilience);
        $requestedProfile = (string) $this->option('profile');
        $selectedProfile = $requestedProfile === 'auto' ? $hardware['recommended_profile'] : $requestedProfile;
        $profileKnown = isset($resilience['execution_profiles'][$selectedProfile]);
        $checks[] = $this->check(
            'Execution profile',
            $selectedProfile.' (recommended '.$hardware['recommended_profile'].')',
            $profileKnown,
        );

        $failed = array_filter($checks, static fn (array $check): bool => ! $check['passed']);

        $this->newLine();
        $this->table(
            ['Check', 'Value', 'Status'],
            array_map(
                static fn (array $check): array => [$check['name'], $check['value'], $check['passed'] ? 'PASS' : 'FAIL'],
                $checks,
            ),
        );

        $this->newLine();
        $this->table(
            ['Execution capability', 'Detected'],
            [
                ['CPU logical cores', (string) $hardware['cpu_cores']],
                ['Memory GiB', number_format($hardware['memory_gib'], 1)],
                ['Free disk GiB', number_format($hardware['disk_free_gib'], 1)],
                ['Architecture', $hardware['architecture']],
                ['Docker CLI', $hardware['docker_available'] ? 'available' : 'unavailable'],
                ['Recommended profile', $hardware['recommended_profile']],
            ],
        );

        if ($hardware['recommended_profile'] === 'light') {
            $this->warn('Constrained environment detected: use the light profile locally and delegate PostgreSQL/browser/full closure evidence to trusted remote or self-hosted verification when available.');
        } else {
            $this->line('Full closure remains a verification responsibility, not a requirement that every developer machine runs every service continuously.');
        }

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

    /** @return array<string,mixed> */
    private function readJson(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string,mixed> $resilience
     *  @return array{cpu_cores:int,memory_gib:float,disk_free_gib:float,architecture:string,docker_available:bool,recommended_profile:string}
     */
    private function hardwareProfile(array $resilience): array
    {
        $cpuRaw = trim((string) shell_exec('getconf _NPROCESSORS_ONLN 2>/dev/null'));
        $cpuCores = ctype_digit($cpuRaw) ? max(1, (int) $cpuRaw) : 1;

        $memoryGib = 0.0;
        if (is_file('/proc/meminfo')) {
            $meminfo = (string) file_get_contents('/proc/meminfo');
            if (preg_match('/^MemTotal:\s+(\d+)\s+kB$/mi', $meminfo, $match) === 1) {
                $memoryGib = ((int) $match[1]) / 1024 / 1024;
            }
        }

        $diskBytes = disk_free_space(base_path());
        $diskFreeGib = is_float($diskBytes) ? $diskBytes / 1024 / 1024 / 1024 : 0.0;
        $dockerAvailable = trim((string) shell_exec('command -v docker 2>/dev/null')) !== '';
        $profiles = is_array($resilience['execution_profiles'] ?? null) ? $resilience['execution_profiles'] : [];

        $recommended = 'standard';
        if ($cpuCores < 4 || ($memoryGib > 0 && $memoryGib < 6.0) || ! $dockerAvailable) {
            $recommended = 'light';
        } elseif ($cpuCores >= 8 && $memoryGib >= 12.0 && isset($profiles['full'])) {
            $recommended = 'standard';
        }

        if (! isset($profiles[$recommended])) {
            $recommended = array_key_first($profiles) ?: 'light';
        }

        return [
            'cpu_cores' => $cpuCores,
            'memory_gib' => $memoryGib,
            'disk_free_gib' => $diskFreeGib,
            'architecture' => php_uname('m'),
            'docker_available' => $dockerAvailable,
            'recommended_profile' => $recommended,
        ];
    }

    /** @return array{name:string,value:string,passed:bool} */
    private function check(string $name, string $value, bool $passed): array
    {
        return compact('name', 'value', 'passed');
    }
}

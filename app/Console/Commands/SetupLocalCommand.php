<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Local\LocalReadinessReport;
use Database\Seeders\CatalogFixtureSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

final class SetupLocalCommand extends Command
{
    protected $signature = 'songchart:setup-local
        {--demo : Seed deterministic local catalog fixtures}
        {--admin : Ensure a local administrator exists without resetting an existing account}
        {--force : Skip the final confirmation in local/testing only}';

    protected $description = 'Prepare a safe local SongChart development environment and print its important URLs.';

    public function handle(LocalReadinessReport $report): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Local bootstrap is disabled outside local/testing environments.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Run local migrations and optional demo bootstrap?', true)) {
            $this->warn('Local bootstrap cancelled.');

            return self::FAILURE;
        }

        try {
            if (! File::exists(base_path('.env'))) {
                if (! File::exists(base_path('.env.example'))) {
                    $this->error('.env.example is missing.');

                    return self::FAILURE;
                }

                File::copy(base_path('.env.example'), base_path('.env'));
                $this->info('Created .env from .env.example.');
            }

            if (! filled(config('app.key'))) {
                Artisan::call('key:generate', ['--force' => true, '--no-interaction' => true]);
                $this->line(trim(Artisan::output()));
            }

            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
            $this->line(trim(Artisan::output()));

            $storageCheck = $report->checks()['storage_link'];

            if (! $storageCheck['ok'] && $storageCheck['detail'] === 'missing') {
                Artisan::call('storage:link', ['--no-interaction' => true]);
                $this->line(trim(Artisan::output()));
            }

            if ($this->option('demo')) {
                Artisan::call('db:seed', [
                    '--class' => CatalogFixtureSeeder::class,
                    '--force' => true,
                    '--no-interaction' => true,
                ]);
                $this->info('Deterministic demo catalog data is ready.');
            }

            if ($this->option('admin')) {
                $exit = $this->call('admin:ensure-local');

                if ($exit !== self::SUCCESS) {
                    return self::FAILURE;
                }
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(['Check', 'State', 'Detail'], collect($report->checks())->map(
            fn (array $check, string $name): array => [$name, $check['ok'] ? 'OK' : 'ACTION', $check['detail']],
        )->values()->all());

        $base = rtrim((string) config('app.url'), '/');
        $this->newLine();
        $this->info('Important local URLs');
        foreach (['/', '/login', '/account', '/account/security', '/admin', '/development/status'] as $path) {
            $this->line($base.$path);
        }
        $this->newLine();
        $this->line('Build frontend assets separately with: npm ci && npm run build');

        return self::SUCCESS;
    }
}

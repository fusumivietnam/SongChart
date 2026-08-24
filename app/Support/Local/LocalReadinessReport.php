<?php

declare(strict_types=1);

namespace App\Support\Local;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class LocalReadinessReport
{
    /** @return array<string, array{ok: bool, detail: string}> */
    public function checks(): array
    {
        return [
            'environment' => [
                'ok' => app()->environment('local', 'testing'),
                'detail' => app()->environment(),
            ],
            'app_url' => [
                'ok' => str_starts_with((string) config('app.url'), 'http'),
                'detail' => (string) config('app.url'),
            ],
            'app_key' => [
                'ok' => filled(config('app.key')),
                'detail' => filled(config('app.key')) ? 'configured' : 'missing',
            ],
            'database' => $this->databaseCheck(),
            'migrations' => $this->migrationCheck(),
            'storage_link' => $this->storageLinkCheck(),
            'vite_build' => [
                'ok' => is_file(public_path('build/manifest.json')),
                'detail' => is_file(public_path('build/manifest.json')) ? 'built' : 'missing; run npm run build',
            ],
        ];
    }

    /** @return array{ok: bool, detail: string} */
    private function storageLinkCheck(): array
    {
        $link = public_path('storage');
        $target = realpath($link);
        $expected = realpath(storage_path('app/public'));

        // PHP on Windows may report a valid NTFS junction as neither is_link()
        // nor is_dir(). realpath() resolves both symbolic links and junctions.
        $available = $target !== false && is_dir($target);

        if (! $available) {
            return ['ok' => false, 'detail' => 'missing'];
        }

        if ($expected !== false && $this->normalizePath($target) !== $this->normalizePath($expected)) {
            return ['ok' => false, 'detail' => 'points to '.$target.'; expected '.$expected];
        }

        return ['ok' => true, 'detail' => 'available'];
    }

    private function normalizePath(string $path): string
    {
        return strtolower(str_replace('\\', '/', rtrim($path, '/\\')));
    }

    /** @return array{ok: bool, detail: string} */
    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true, 'detail' => (string) config('database.default')];
        } catch (Throwable $exception) {
            return ['ok' => false, 'detail' => $exception->getMessage()];
        }
    }

    /** @return array{ok: bool, detail: string} */
    private function migrationCheck(): array
    {
        try {
            if (! Schema::hasTable('migrations')) {
                return ['ok' => false, 'detail' => 'migrations table missing'];
            }

            Artisan::call('migrate:status', ['--no-interaction' => true]);
            $output = Artisan::output();

            return [
                'ok' => ! str_contains($output, 'Pending'),
                'detail' => str_contains($output, 'Pending') ? 'pending migrations' : 'up to date',
            ];
        } catch (Throwable $exception) {
            return ['ok' => false, 'detail' => $exception->getMessage()];
        }
    }
}

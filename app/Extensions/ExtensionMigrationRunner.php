<?php

declare(strict_types=1);

namespace App\Extensions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

final class ExtensionMigrationRunner
{
    public function __construct(private Filesystem $files) {}

    /** @return array{ran:bool,path:?string,output:string} */
    public function migrate(string $releasePath): array
    {
        $path = base_path($releasePath.'/database/migrations');
        if (! is_dir($path) || count($this->files->files($path)) === 0) {
            return ['ran' => false, 'path' => null, 'output' => 'No extension migrations.'];
        }

        $exit = Artisan::call('migrate', ['--path' => $this->relative($path), '--force' => true]);
        if ($exit !== 0) {
            throw new RuntimeException('Extension migration failed: '.Artisan::output());
        }

        return ['ran' => true, 'path' => $this->relative($path), 'output' => Artisan::output()];
    }

    /** @return array{ran:bool,path:?string,output:string} */
    public function rollback(string $releasePath): array
    {
        $path = base_path($releasePath.'/database/migrations');
        if (! is_dir($path) || count($this->files->files($path)) === 0) {
            return ['ran' => false, 'path' => null, 'output' => 'No extension migrations.'];
        }

        $exit = Artisan::call('migrate:rollback', ['--path' => $this->relative($path), '--force' => true]);
        if ($exit !== 0) {
            throw new RuntimeException('Extension migration rollback failed: '.Artisan::output());
        }

        return ['ran' => true, 'path' => $this->relative($path), 'output' => Artisan::output()];
    }

    private function relative(string $absolute): string
    {
        return str_replace('\\', '/', ltrim(str_replace(base_path(), '', $absolute), '/\\'));
    }
}

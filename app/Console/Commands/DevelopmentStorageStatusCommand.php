<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Development\DevelopmentStorageAuthority;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Throwable;

final class DevelopmentStorageStatusCommand extends Command
{
    protected $signature = 'development:storage-status {--json}';

    protected $description = 'Inspect the configured SongChart development object-storage authority.';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            return $this->failCommand('Development storage diagnostics are available only in the local environment.');
        }

        $configuration = (array) config('songchart.development.storage', []);
        $mode = (string) ($configuration['mode'] ?? '');
        $adapterAvailable = class_exists(AwsS3V3Adapter::class);

        try {
            DevelopmentStorageAuthority::assertSafe($configuration, $adapterAvailable);
        } catch (Throwable $exception) {
            return $this->failCommand($exception->getMessage(), [
                'mode' => $mode,
                'adapter_available' => $adapterAvailable,
            ]);
        }

        $disk = (string) ($configuration['disk'] ?? 'local');
        $endpoint = trim((string) ($configuration['endpoint'] ?? ''));
        $endpointHost = $endpoint !== '' ? (string) parse_url($endpoint, PHP_URL_HOST) : null;
        $bucket = trim((string) ($configuration['bucket'] ?? ''));

        if ($mode === 'remote') {
            try {
                Storage::disk($disk)->exists('__songchart_diagnostics__/read-only-probe');
            } catch (Throwable $exception) {
                return $this->failCommand(
                    'Configured development object storage is unreachable: '.$exception->getMessage(),
                    [
                        'mode' => $mode,
                        'disk' => $disk,
                        'adapter_available' => $adapterAvailable,
                        'endpoint_host' => $endpointHost,
                        'bucket' => $bucket,
                    ],
                );
            }
        }

        $payload = [
            'ok' => true,
            'mode' => $mode,
            'disk' => $disk,
            'adapter_available' => $adapterAvailable,
            'reachable' => true,
            'endpoint_host' => $endpointHost,
            'bucket' => $bucket !== '' ? $bucket : null,
            'secrets_exposed' => false,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ));

            return self::SUCCESS;
        }

        $this->components->info('SongChart development storage authority');
        $this->table(
            ['Property', 'Value'],
            [
                ['Mode', $payload['mode']],
                ['Disk', $payload['disk']],
                ['S3 adapter available', $payload['adapter_available'] ? 'yes' : 'no'],
                ['Reachable', 'yes'],
                ['Endpoint host', $payload['endpoint_host'] ?? '(local)'],
                ['Bucket', $payload['bucket'] ?? '(local)'],
                ['Secrets exposed', 'no'],
            ],
        );

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $payload */
    private function failCommand(string $message, array $payload = []): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode(
                [
                    'ok' => false,
                    'error' => $message,
                    ...$payload,
                    'secrets_exposed' => false,
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ));

            return self::FAILURE;
        }

        $this->components->error($message);

        return self::FAILURE;
    }
}

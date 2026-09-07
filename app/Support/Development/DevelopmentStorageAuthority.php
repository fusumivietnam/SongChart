<?php

declare(strict_types=1);

namespace App\Support\Development;

use RuntimeException;

final class DevelopmentStorageAuthority
{
    /**
     * @param  array<string, mixed>  $configuration
     */
    public static function assertSafe(array $configuration, bool $s3AdapterAvailable): void
    {
        $mode = (string) ($configuration['mode'] ?? '');

        if (! in_array($mode, ['local', 'remote'], true)) {
            throw new RuntimeException(
                'Development storage mode must explicitly be local or remote.',
            );
        }

        if ($mode === 'local') {
            return;
        }

        if (! $s3AdapterAvailable) {
            throw new RuntimeException(
                'Remote development storage mode requires the governed Laravel S3 filesystem adapter.',
            );
        }

        foreach (['disk', 'bucket', 'endpoint', 'key', 'secret'] as $required) {
            if (trim((string) ($configuration[$required] ?? '')) === '') {
                throw new RuntimeException(
                    sprintf('Remote development storage mode requires [%s].', $required),
                );
            }
        }

        $disk = (string) $configuration['disk'];
        if ($disk !== 'r2') {
            throw new RuntimeException(
                'Remote development storage mode must use the governed [r2] Laravel filesystem disk.',
            );
        }

        $endpoint = (string) $configuration['endpoint'];
        $scheme = strtolower((string) parse_url($endpoint, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($endpoint, PHP_URL_HOST));

        if ($scheme !== 'https' || $host === '') {
            throw new RuntimeException(
                'Remote development storage mode requires an HTTPS object-storage endpoint.',
            );
        }

        foreach (['localhost', '127.0.0.1'] as $localHost) {
            if ($host === $localHost) {
                throw new RuntimeException(
                    'Remote development storage mode must not resolve to local filesystem/object-storage authority.',
                );
            }
        }
    }
}

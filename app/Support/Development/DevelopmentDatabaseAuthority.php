<?php

declare(strict_types=1);

namespace App\Support\Development;

use RuntimeException;

final class DevelopmentDatabaseAuthority
{
    /**
     * @param  array<string, mixed>  $configuration
     */
    public static function assertSafe(array $configuration): void
    {
        $mode = (string) ($configuration['mode'] ?? '');

        if (! in_array($mode, ['local', 'remote'], true)) {
            throw new RuntimeException(
                'Development database mode must explicitly be local or remote.',
            );
        }

        if ($mode === 'local') {
            return;
        }

        self::assertRemote($configuration);
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    private static function assertRemote(array $configuration): void
    {
        $url = trim((string) ($configuration['url'] ?? ''));

        if ($url === '') {
            throw new RuntimeException(
                'Remote development database mode requires DB_URL.',
            );
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (
            $host === ''
            || in_array($host, ['localhost', '127.0.0.1', 'postgres'], true)
        ) {
            throw new RuntimeException(
                'Remote development database must not resolve to a local PostgreSQL host.',
            );
        }

        $sslmode = strtolower((string) ($configuration['sslmode'] ?? ''));

        if (! in_array($sslmode, ['require', 'verify-ca', 'verify-full'], true)) {
            throw new RuntimeException(
                'Remote development database mode requires PostgreSQL TLS.',
            );
        }
    }
}

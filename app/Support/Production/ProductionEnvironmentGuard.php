<?php

declare(strict_types=1);

namespace App\Support\Production;

use LogicException;

final class ProductionEnvironmentGuard
{
    /**
     * @param array{
     *   app_debug: bool,
     *   app_url: string,
     *   database: string,
     *   cache: string,
     *   queue: string,
     *   session_secure: bool|null,
     *   admin_2fa_mode: string,
     *   design_lab_enabled: bool
     * } $state
     */
    public static function assertSafe(array $state): void
    {
        $issues = [];

        if ($state['app_debug']) {
            $issues[] = 'APP_DEBUG must be false';
        }

        if (! str_starts_with(strtolower($state['app_url']), 'https://')) {
            $issues[] = 'APP_URL must use https';
        }

        if ($state['database'] !== 'pgsql') {
            $issues[] = 'DB_CONNECTION must be pgsql';
        }

        if ($state['cache'] !== 'redis') {
            $issues[] = 'CACHE_STORE must be redis';
        }

        if ($state['queue'] !== 'redis') {
            $issues[] = 'QUEUE_CONNECTION must be redis';
        }

        if ($state['session_secure'] !== true) {
            $issues[] = 'SESSION_SECURE_COOKIE must be true';
        }

        if ($state['admin_2fa_mode'] !== 'required') {
            $issues[] = 'SONGCHART_ADMIN_2FA_MODE must be required';
        }

        if ($state['design_lab_enabled']) {
            $issues[] = 'DESIGN_LAB_ENABLED must be false';
        }

        if ($issues !== []) {
            throw new LogicException('Unsafe production environment: '.implode('; ', $issues).'.');
        }
    }
}

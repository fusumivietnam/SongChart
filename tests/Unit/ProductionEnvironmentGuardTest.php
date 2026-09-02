<?php

declare(strict_types=1);

use App\Support\Production\ProductionEnvironmentGuard;

function safeProductionEnvironmentState(array $overrides = []): array
{
    return array_replace([
        'app_debug' => false,
        'app_url' => 'https://songchart.example',
        'database' => 'pgsql',
        'cache' => 'redis',
        'queue' => 'redis',
        'session_secure' => true,
        'admin_2fa_mode' => 'required',
        'design_lab_enabled' => false,
    ], $overrides);
}

it('accepts the supported production environment baseline', function (): void {
    ProductionEnvironmentGuard::assertSafe(safeProductionEnvironmentState());

    expect(true)->toBeTrue();
});

it('fails closed when a production safety invariant drifts', function (array $override, string $expected): void {
    expect(fn () => ProductionEnvironmentGuard::assertSafe(safeProductionEnvironmentState($override)))
        ->toThrow(\LogicException::class, $expected);
})->with([
    [['app_debug' => true], 'APP_DEBUG must be false'],
    [['app_url' => 'http://songchart.example'], 'APP_URL must use https'],
    [['database' => 'sqlite'], 'DB_CONNECTION must be pgsql'],
    [['cache' => 'database'], 'CACHE_STORE must be redis'],
    [['queue' => 'sync'], 'QUEUE_CONNECTION must be redis'],
    [['session_secure' => false], 'SESSION_SECURE_COOKIE must be true'],
    [['admin_2fa_mode' => 'disabled'], 'SONGCHART_ADMIN_2FA_MODE must be required'],
    [['design_lab_enabled' => true], 'DESIGN_LAB_ENABLED must be false'],
]);

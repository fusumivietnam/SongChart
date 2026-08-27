<?php

declare(strict_types=1);

it('keeps candidate read-only and exposes optimized closure and demo workflows', function (): void {
    $cli = file_get_contents(base_path('songchart'));
    $dev = file_get_contents(base_path('compose.dev.yml'));
    $demo = file_get_contents(base_path('compose.demo.yml'));
    $demoEnv = file_get_contents(base_path('.env.demo.example'));
    $databaseConfig = file_get_contents(base_path('config/database.php'));
    $queueConfig = file_get_contents(base_path('config/queue.php'));
    $appProvider = file_get_contents(base_path('app/Providers/AppServiceProvider.php'));
    $twoFactorMiddleware = file_get_contents(base_path('app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php'));
    $adminSidebar = file_get_contents(base_path('resources/views/components/admin/sidebar.blade.php'));
    $adminDashboard = file_get_contents(base_path('resources/views/admin/dashboard.blade.php'));
    $providerAdmin = file_get_contents(base_path('resources/views/admin/operations/providers.blade.php'));
    $systemAdmin = file_get_contents(base_path('resources/views/admin/operations/system.blade.php'));
    $providerConfigurationRequest = file_get_contents(base_path('app/Http/Requests/Admin/ProviderConfigurationRequest.php'));
    $providerConfigurationController = file_get_contents(base_path('app/Http/Controllers/Admin/ProviderConfigurationController.php'));
    $providerConfigurationService = file_get_contents(base_path('app/Support/Providers/Operations/ProviderConfigurationService.php'));
    $credentialResolver = file_get_contents(base_path('app/Support/Providers/Configuration/ProviderCredentialResolver.php'));
    $youtubeDiscovery = file_get_contents(base_path('app/Support/Providers/Destinations/YouTubeVideoDestinationDiscovery.php'));
    $credentialMigration = file_get_contents(base_path('database/migrations/2026_08_27_000100_create_provider_credentials_table.php'));

    expect($cli)
        ->toContain('./songchart close')
        ->toContain('canonical owns the stage lane exactly once')
        ->toContain('./songchart dev test --no-build <path>')
        ->toContain('./songchart dev db backup')
        ->toContain('./songchart demo setup')
        ->toContain('demo up -d redis app')
        ->toContain('dev up -d postgres redis app queue')
        ->toContain('single shared development queue worker authority')
        ->toContain('find /workspace/node_modules -mindepth 1 -maxdepth 1 -exec rm -rf {} +')
        ->toContain('/tmp/composer-cache /tmp/npm-cache')
        ->toContain('chown -R $SONGCHART_HOST_UID:$SONGCHART_HOST_GID')
        ->toContain('APP_URL='."' + demo_url")
        ->toContain('SONGCHART_DEMO_APP_URL="https://${CODESPACE_NAME}-8001.')
        ->not->toContain('demo up -d redis app queue');

    expect($demoEnv)
        ->toContain("APP_URL=\n")
        ->toContain('SONGCHART_ADMIN_2FA_MODE=disabled')
        ->not->toContain('APP_URL=http://127.0.0.1:8001')
        ->not->toContain('APP_URL=http://localhost:8001');

    expect($databaseConfig)
        ->toContain("'queue' => [")
        ->toContain("env('REDIS_QUEUE_HOST'")
        ->toContain("env('REDIS_QUEUE_DB', '0')");

    expect($queueConfig)
        ->toContain("env('REDIS_QUEUE_CONNECTION', 'queue')");

    expect($dev)
        ->toContain('songchart-shared-queue')
        ->toContain('REDIS_QUEUE_CONNECTION: queue')
        ->toContain('REDIS_QUEUE_HOST: redis');

    expect($appProvider)
        ->toContain("environment('demo')")
        ->toContain('URL::forceRootUrl($demoUrl)')
        ->toContain('URL::forceScheme($scheme)')
        ->toContain('Vite::createAssetPathsUsing')
        ->toContain("'/'.ltrim(\$path, '/')");

    expect($twoFactorMiddleware)
        ->toContain('$twoFactorMode = (string) config(\'songchart.security.admin_2fa_mode\', \'required\')')
        ->toContain("app()->environment(['local', 'demo', 'testing'])")
        ->toContain('$requiresTwoFactor = $twoFactorMode === \'required\' || ! $mayDisableTwoFactor');

    expect($adminSidebar)
        ->toContain("'label' => 'Nguồn dữ liệu'")
        ->toContain("'label' => 'Thiết lập hệ thống'")
        ->toContain("route('admin.providers.index')")
        ->toContain("route('admin.system.index')");

    expect($adminDashboard)
        ->toContain('Thiết lập API & tích hợp')
        ->toContain("route('admin.system.index')")
        ->toContain('#api-integrations')
        ->toContain('manage-providers');

    expect($providerAdmin)
        ->toContain('Cấu hình API nằm trong Thiết lập hệ thống')
        ->toContain('Thiết lập hệ thống → API & tích hợp')
        ->toContain("route('admin.system.index')")
        ->not->toContain('name="musicbrainz_user_agent"')
        ->not->toContain('name="youtube_api_keys"');

    expect($systemAdmin)
        ->toContain('id="api-integrations"')
        ->toContain('API & tích hợp')
        ->toContain('musicbrainz_user_agent')
        ->toContain('youtube_api_keys')
        ->toContain('credential pool')
        ->toContain('provider_operational_state')
        ->toContain('Lưu cấu hình & trạng thái')
        ->toContain('.env fallback')
        ->toContain("route('admin.providers.configuration.update'")
        ->toContain('manage-providers');

    expect($providerConfigurationRequest)
        ->toContain('youtube_api_keys')
        ->toContain('provider_operational_state')
        ->toContain("Rule::in(['enabled', 'disabled'])");

    expect($providerConfigurationController)
        ->toContain("\$credentialPools['api_key']")
        ->toContain("enabled: (string) \$validated['provider_operational_state'] === 'enabled'");

    expect($providerConfigurationService)
        ->toContain('array $credentialPools')
        ->toContain('ProviderCredential::query()')
        ->toContain("'is_enabled' => \$enabled")
        ->toContain('credential_pool_counts');

    expect($credentialResolver)
        ->toContain("where('is_enabled', true)")
        ->toContain("whereNull('cooldown_until')")
        ->toContain("orderByRaw('last_used_at asc nulls first')")
        ->toContain('Crypt::decryptString');

    expect($youtubeDiscovery)
        ->toContain('ProviderCredentialResolver')
        ->toContain("\$this->credentials->resolve('youtube', 'api_key'")
        ->toContain("\$this->quota->consume('search.list')")
        ->toContain("\$this->quota->consume('videos.list')");

    expect($credentialMigration)
        ->toContain("Schema::create('provider_credentials'")
        ->toContain('encrypted_secret')
        ->toContain('cooldown_until')
        ->toContain('last_used_at');

    expect(preg_match('/^ candidate\)\n(?<block>.*?)^ close\)\n/ms', $cli, $candidateMatch))->toBe(1);
    $candidateBlock = (string) ($candidateMatch['block'] ?? '');
    expect($candidateBlock)
        ->toContain('git -C "$ROOT" diff --check')
        ->not->toContain('refresh_candidate_authority');

    expect(preg_match('/^ close\)\n(?<block>.*?)^ test\) /ms', $cli, $closeMatch))->toBe(1);
    $closeBlock = (string) ($closeMatch['block'] ?? '');
    expect($closeBlock)
        ->toContain('prepare_candidate_authority')
        ->toContain('verify_compose run --rm verify')
        ->not->toContain('stage_verify');

    expect($demo)
        ->toContain('APP_URL: "${SONGCHART_DEMO_APP_URL}"')
        ->toContain('php artisan migrate --force')
        ->toContain('DB_DATABASE: songchart_docker')
        ->toContain('REDIS_QUEUE_CONNECTION: queue')
        ->toContain('REDIS_QUEUE_HOST: songchart-shared-queue')
        ->toContain('name: "${SONGCHART_DEV_PROJECT:-songchart-dev}_default"')
        ->not->toContain("\n  queue:\n")
        ->not->toContain('songchart_verify_test');
});

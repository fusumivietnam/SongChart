<?php

declare(strict_types=1);

it('keeps candidate read-only and exposes optimized closure and demo workflows', function (): void {
    $cli = file_get_contents(base_path('songchart'));
    $demo = file_get_contents(base_path('compose.demo.yml'));
    $demoEnv = file_get_contents(base_path('.env.demo.example'));
    $appProvider = file_get_contents(base_path('app/Providers/AppServiceProvider.php'));
    $twoFactorMiddleware = file_get_contents(base_path('app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php'));
    $adminSidebar = file_get_contents(base_path('resources/views/components/admin/sidebar.blade.php'));
    $adminDashboard = file_get_contents(base_path('resources/views/admin/dashboard.blade.php'));
    $providerAdmin = file_get_contents(base_path('resources/views/admin/operations/providers.blade.php'));

    expect($cli)
        ->toContain('./songchart close')
        ->toContain('canonical owns the stage lane exactly once')
        ->toContain('./songchart dev test --no-build <path>')
        ->toContain('./songchart dev db backup')
        ->toContain('./songchart demo setup')
        ->toContain('demo up -d redis app')
        ->toContain('find /workspace/node_modules -mindepth 1 -maxdepth 1 -exec rm -rf {} +')
        ->toContain('/tmp/composer-cache /tmp/npm-cache')
        ->toContain('chown -R $SONGCHART_HOST_UID:$SONGCHART_HOST_GID')
        ->toContain('APP_URL=' . "' + demo_url")
        ->toContain('SONGCHART_DEMO_APP_URL="https://${CODESPACE_NAME}-8001.')
        ->not->toContain('demo up -d redis app queue');

    expect($demoEnv)
        ->toContain("APP_URL=\n")
        ->toContain('SONGCHART_ADMIN_2FA_MODE=disabled')
        ->not->toContain('APP_URL=http://127.0.0.1:8001')
        ->not->toContain('APP_URL=http://localhost:8001');

    expect($appProvider)
        ->toContain("environment('demo')")
        ->toContain('URL::forceRootUrl($demoUrl)')
        ->toContain('URL::forceScheme($scheme)')
        ->toContain('Vite::createAssetPathsUsing')
        ->toContain("'/'.ltrim(\$path, '/')");

    expect($twoFactorMiddleware)
        ->toContain("config('songchart.security.admin_2fa_mode', 'required') === 'required'")
        ->not->toContain("! app()->environment('local')");

    expect($adminSidebar)
        ->toContain("'label' => 'Nguồn dữ liệu & API'")
        ->toContain("route('admin.providers.index')");

    expect($adminDashboard)
        ->toContain('Cấu hình nguồn dữ liệu')
        ->toContain('Nguồn dữ liệu & API')
        ->toContain('manage-providers');

    expect($providerAdmin)
        ->toContain('Cấu hình nguồn dữ liệu & API')
        ->toContain('musicbrainz_user_agent')
        ->toContain('youtube_api_key')
        ->toContain('Admin DB')
        ->toContain('.env')
        ->toContain('Không cần nhớ biến môi trường')
        ->toContain('Trạng thái, bật/tắt và test nguồn')
        ->toContain('provider_manager')
        ->toContain('super_admin');

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
        ->toContain('DB_DATABASE: songchart_docker')
        ->toContain('name: "${SONGCHART_DEV_PROJECT:-songchart-dev}_default"')
        ->not->toContain('songchart_verify_test');
});

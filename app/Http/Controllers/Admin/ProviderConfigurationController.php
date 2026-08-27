<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProviderConfigurationRequest;
use App\Models\Provider;
use App\Models\User;
use App\Support\Providers\Operations\ProviderConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

final class ProviderConfigurationController extends Controller
{
    public function __invoke(
        ProviderConfigurationRequest $request,
        Provider $provider,
        ProviderConfigurationService $configuration,
    ): RedirectResponse {
        $validated = $request->validated();
        /** @var User $actor */
        $actor = $request->user();

        $settings = [];
        $secrets = [];
        $credentialPools = [];

        if ($provider->slug === 'musicbrainz') {
            $settings['user_agent'] = trim((string) ($validated['musicbrainz_user_agent'] ?? ''));
        } elseif ($provider->slug === 'youtube') {
            $poolText = trim((string) ($validated['youtube_api_keys'] ?? ''));
            if ($poolText !== '') {
                $credentialPools['api_key'] = array_values(array_filter(array_map(
                    static fn (string $value): string => trim($value),
                    preg_split('/\R+/', $poolText) ?: [],
                ), static fn (string $value): bool => $value !== ''));
            }
        } else {
            throw ValidationException::withMessages([
                'provider' => 'Nguồn dữ liệu này chưa có adapter cấu hình vận hành qua Admin UI.',
            ]);
        }

        $configuration->apply(
            provider: $provider,
            settings: $settings,
            secrets: $secrets,
            credentialPools: $credentialPools,
            enabled: (string) $validated['provider_operational_state'] === 'enabled',
            actor: $actor,
            rationale: (string) $validated['rationale'],
            idempotencyKey: (string) $validated['idempotency_key'],
        );

        return back()->with('status', 'Đã lưu cấu hình, credential pool và trạng thái vận hành của nguồn dữ liệu.');
    }
}

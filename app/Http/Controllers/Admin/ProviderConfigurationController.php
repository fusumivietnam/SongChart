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

        if ($provider->slug === 'musicbrainz') {
            $settings['user_agent'] = trim((string) ($validated['musicbrainz_user_agent'] ?? ''));
        } elseif ($provider->slug === 'youtube') {
            $apiKey = trim((string) ($validated['youtube_api_key'] ?? ''));
            $secrets['api_key'] = $apiKey !== '' ? $apiKey : null;
        } else {
            throw ValidationException::withMessages([
                'provider' => 'Nguồn dữ liệu này chưa có thiết lập vận hành qua giao diện ở Stage 18.1.',
            ]);
        }

        $configuration->apply(
            provider: $provider,
            settings: $settings,
            secrets: $secrets,
            actor: $actor,
            rationale: (string) $validated['rationale'],
            idempotencyKey: (string) $validated['idempotency_key'],
        );

        return back()->with('status', 'Đã lưu thiết lập nguồn dữ liệu. Thay đổi sẽ được dùng cho các yêu cầu provider tiếp theo.');
    }
}

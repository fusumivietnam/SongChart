<?php

declare(strict_types=1);

namespace App\Support\Providers\Configuration;

use App\Models\Provider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Throwable;

final class ProviderRuntimeConfiguration
{
    public function apply(string $providerSlug): void
    {
        if ($providerSlug === 'musicbrainz') {
            Config::set('songchart.providers.musicbrainz.enabled', $this->enabled(
                'musicbrainz',
                (bool) config('songchart.providers.musicbrainz.enabled', false),
            ));
            Config::set('songchart.providers.musicbrainz.user_agent', $this->setting(
                'musicbrainz',
                'user_agent',
                config('songchart.providers.musicbrainz.user_agent'),
            ));

            return;
        }

        if ($providerSlug === 'youtube') {
            Config::set('songchart.providers.youtube.enabled', $this->enabled(
                'youtube',
                (bool) config('songchart.providers.youtube.enabled', false),
            ));
            Config::set('songchart.providers.youtube.api_key', $this->secret(
                'youtube',
                'api_key',
                (string) config('songchart.providers.youtube.api_key', ''),
            ));
        }
    }

    public function enabled(string $providerSlug, bool $fallback = false): bool
    {
        $provider = $this->provider($providerSlug);

        return $provider?->is_enabled ?? $fallback;
    }

    public function setting(string $providerSlug, string $key, mixed $fallback = null): mixed
    {
        $provider = $this->provider($providerSlug);
        $configuration = is_array($provider?->configuration) ? $provider->configuration : [];

        return $configuration[$key] ?? $fallback;
    }

    public function secret(string $providerSlug, string $key, ?string $fallback = null): ?string
    {
        $provider = $this->provider($providerSlug);
        $configuration = is_array($provider?->configuration) ? $provider->configuration : [];
        $secrets = is_array($configuration['_secrets'] ?? null) ? $configuration['_secrets'] : [];
        $encrypted = $secrets[$key] ?? null;

        if (! is_string($encrypted) || $encrypted === '') {
            return $fallback;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable) {
            return $fallback;
        }
    }

    public function hasSecret(string $providerSlug, string $key): bool
    {
        $provider = $this->provider($providerSlug);
        $configuration = is_array($provider?->configuration) ? $provider->configuration : [];
        $secrets = is_array($configuration['_secrets'] ?? null) ? $configuration['_secrets'] : [];

        return is_string($secrets[$key] ?? null) && $secrets[$key] !== '';
    }

    private function provider(string $providerSlug): ?Provider
    {
        return Provider::query()->where('slug', $providerSlug)->first();
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Providers\Configuration;

use App\Models\Provider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
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

        return $provider === null ? $fallback : $provider->is_enabled;
    }

    public function setting(string $providerSlug, string $key, mixed $fallback = null): mixed
    {
        $configuration = $this->configuration($providerSlug);

        return $configuration[$key] ?? $fallback;
    }

    public function secret(string $providerSlug, string $key, ?string $fallback = null): ?string
    {
        $configuration = $this->configuration($providerSlug);
        $secrets = $configuration['_secrets'] ?? null;
        if (! is_array($secrets)) {
            return $fallback;
        }

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
        $configuration = $this->configuration($providerSlug);
        $secrets = $configuration['_secrets'] ?? null;
        if (! is_array($secrets)) {
            return false;
        }

        $secret = $secrets[$key] ?? null;

        return is_string($secret) && $secret !== '';
    }

    /** @return array<string, mixed> */
    private function configuration(string $providerSlug): array
    {
        $provider = $this->provider($providerSlug);

        return $provider->configuration ?? [];
    }

    private function provider(string $providerSlug): ?Provider
    {
        return Provider::query()->where('slug', $providerSlug)->first();
    }
}

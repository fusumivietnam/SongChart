<?php

declare(strict_types=1);

namespace App\Support\Providers;

final class ProviderDestinationPolicy
{
    /** @var array<string,list<string>> */
    private const ALLOWED_HOSTS = [
        'youtube' => ['youtube.com', 'www.youtube.com', 'music.youtube.com', 'youtu.be'],
        'spotify' => ['open.spotify.com'],
        'soundcloud' => ['soundcloud.com', 'www.soundcloud.com'],
        'apple_music' => ['music.apple.com'],
        'deezer' => ['deezer.com', 'www.deezer.com'],
    ];

    /** @param array<string,mixed> $provider */
    public function canOpen(array $provider): bool
    {
        if (($provider['status'] ?? null) !== 'available') {
            return false;
        }

        if (($provider['compliance_state'] ?? null) !== 'approved') {
            return false;
        }

        $url = $provider['url'] ?? null;
        if (! is_string($url) || $url === '') {
            return false;
        }

        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || ! isset($parts['host'])) {
            return false;
        }

        $key = (string) ($provider['key'] ?? '');
        $host = strtolower((string) $parts['host']);

        return in_array($host, self::ALLOWED_HOSTS[$key] ?? [], true);
    }
}

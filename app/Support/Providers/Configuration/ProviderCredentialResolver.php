<?php

declare(strict_types=1);

namespace App\Support\Providers\Configuration;

use App\Models\Provider;
use App\Models\Providers\ProviderCredential;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

final class ProviderCredentialResolver
{
    public function resolve(string $providerSlug, string $kind, ?string $fallback = null): string
    {
        $provider = Provider::query()->where('slug', $providerSlug)->first();
        if ($provider === null) {
            if ($fallback !== null && $fallback !== '') {
                return $fallback;
            }

            throw new RuntimeException("Provider [{$providerSlug}] is not registered.");
        }

        $credential = ProviderCredential::query()
            ->where('provider_id', $provider->getKey())
            ->where('kind', $kind)
            ->where('is_enabled', true)
            ->where(function ($query): void {
                $query->whereNull('cooldown_until')->orWhere('cooldown_until', '<=', now());
            })
            ->orderBy('priority')
            ->orderByRaw('last_used_at asc nulls first')
            ->orderBy('id')
            ->first();

        if ($credential === null) {
            if ($fallback !== null && $fallback !== '') {
                return $fallback;
            }

            throw new RuntimeException("No healthy credential is available for provider [{$providerSlug}].");
        }

        $secret = Crypt::decryptString($credential->encrypted_secret);
        $credential->forceFill(['last_used_at' => now()])->save();

        return $secret;
    }

    public function configuredCount(string $providerSlug, string $kind): int
    {
        $provider = Provider::query()->where('slug', $providerSlug)->first();
        if ($provider === null) {
            return 0;
        }

        return ProviderCredential::query()
            ->where('provider_id', $provider->getKey())
            ->where('kind', $kind)
            ->count();
    }
}

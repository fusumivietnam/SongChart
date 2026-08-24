<?php

declare(strict_types=1);

namespace App\Contracts\Providers;

interface ProviderAdapter
{
    public function providerSlug(): string;

    /** @return list<string> */
    public function capabilities(): array;

    public function healthCheck(): ProviderHealth;
}

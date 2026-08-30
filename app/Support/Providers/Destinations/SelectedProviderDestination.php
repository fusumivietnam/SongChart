<?php

declare(strict_types=1);

namespace App\Support\Providers\Destinations;

use App\Models\ProviderDestination;

final readonly class SelectedProviderDestination
{
    public function __construct(
        public ProviderDestination $destination,
        public bool $fresh,
        public bool $canEmbed,
    ) {}
}

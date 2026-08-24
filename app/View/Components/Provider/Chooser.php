<?php

declare(strict_types=1);

namespace App\View\Components\Provider;

use App\Support\Providers\ProviderDestinationPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Chooser extends Component
{
    /** @var array<string, mixed> */
    public array $entity;

    /** @var list<array<string, mixed>> */
    public array $providers = [];

    /**
     * @param  array<string, mixed>  $entity
     */
    public function __construct(array $entity, ProviderDestinationPolicy $destinationPolicy)
    {
        $this->entity = $entity;
        $providers = $entity['providers'] ?? [];

        if (! is_array($providers)) {
            return;
        }

        foreach ($providers as $provider) {
            if (! is_array($provider)) {
                continue;
            }

            $this->providers[] = [
                ...$provider,
                'can_open' => $destinationPolicy->canOpen($provider),
            ];
        }
    }

    public function render(): View
    {
        return view('components.provider.chooser');
    }
}

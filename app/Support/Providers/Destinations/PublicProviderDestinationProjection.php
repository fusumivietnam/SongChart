<?php

declare(strict_types=1);

namespace App\Support\Providers\Destinations;

final readonly class PublicProviderDestinationProjection
{
    public const STATE_PLAYABLE = 'playable';

    public const STATE_OUTBOUND_ONLY = 'outbound_only';

    public const STATE_NO_SELECTION = 'no_selection';

    public const REASON_SELECTED_EMBEDDABLE = 'selected_eligible_embeddable';

    public const REASON_SELECTED_OUTBOUND_ONLY = 'selected_eligible_outbound_only';

    /** @param list<string> $reasonCodes */
    public function __construct(
        public string $state,
        public ?SelectedProviderDestination $selection,
        public array $reasonCodes,
    ) {}
}

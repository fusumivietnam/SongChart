<?php

declare(strict_types=1);

namespace App\Application\Discovery\Commands;

use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoveryLayout;
use App\Domain\Discovery\ValueObjects\DiscoveryPublicationWindow;

final readonly class UpdateDiscoveryChannel
{
    /** @param list<DiscoverySort> $sorts */
    public function __construct(
        public string $channelId,
        public string $slug,
        public string $name,
        public ?string $description,
        public ?DiscoveryRuleSet $rules,
        public array $sorts,
        public DiscoveryLayout $layout,
        public int $defaultLimit,
        public DiscoveryPublicationWindow $publication,
        public ?string $actorUserId,
    ) {}
}

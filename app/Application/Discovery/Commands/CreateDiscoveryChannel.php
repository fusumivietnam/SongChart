<?php

declare(strict_types=1);

namespace App\Application\Discovery\Commands;

use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryChannelMode;
use App\Domain\Discovery\Enums\DiscoveryLayout;
use App\Domain\Discovery\ValueObjects\DiscoveryPublicationWindow;

final readonly class CreateDiscoveryChannel
{
    /** @param list<DiscoverySort> $sorts */
    public function __construct(
        public string $key,
        public string $slug,
        public string $name,
        public ?string $description,
        public DiscoverableEntityType $entityType,
        public DiscoveryChannelMode $mode,
        public ?DiscoveryRuleSet $rules,
        public array $sorts,
        public DiscoveryLayout $layout,
        public int $defaultLimit,
        public DiscoveryPublicationWindow $publication,
        public ?string $actorUserId,
    ) {}
}

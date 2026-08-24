<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\DiscoveryFieldDefinition;
use App\Domain\Discovery\Enums\DiscoverableEntityType;

interface DiscoveryFieldRegistry
{
    public function get(DiscoverableEntityType $entityType, string $field): DiscoveryFieldDefinition;

    public function has(DiscoverableEntityType $entityType, string $field): bool;

    /** @return list<DiscoveryFieldDefinition> */
    public function allFor(DiscoverableEntityType $entityType): array;
}

<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoverableEntityType;
use Closure;
use InvalidArgumentException;

final readonly class CompiledDiscoveryRule
{
    /** @param Closure(DiscoveryEntitySnapshot): bool $predicate */
    public function __construct(
        public DiscoverableEntityType $entityType,
        private Closure $predicate,
    ) {}

    public function matches(DiscoveryEntitySnapshot $entity): bool
    {
        if ($entity->entityType !== $this->entityType) {
            throw new InvalidArgumentException('Compiled discovery rule cannot evaluate a different entity type.');
        }

        return ($this->predicate)($entity);
    }
}

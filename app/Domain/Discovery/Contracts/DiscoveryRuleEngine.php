<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\CompiledDiscoveryRule;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;

interface DiscoveryRuleEngine
{
    public function compile(DiscoverableEntityType $entityType, DiscoveryRuleSet $rules): CompiledDiscoveryRule;

    /** @param  list<DiscoverySort>  $sorts */
    public function validateSorts(DiscoverableEntityType $entityType, array $sorts): void;

    public function matches(DiscoveryEntitySnapshot $entity, DiscoveryRuleSet $rules): bool;

    /**
     * @param  list<DiscoveryEntitySnapshot>  $entities
     * @param  list<DiscoverySort>  $sorts
     * @return list<DiscoveryEntitySnapshot>
     */
    public function sort(array $entities, array $sorts): array;
}

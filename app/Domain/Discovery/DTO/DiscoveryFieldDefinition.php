<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;

final readonly class DiscoveryFieldDefinition
{
    /**
     * @param  list<DiscoveryRuleOperator>  $allowedOperators
     * @param  list<DiscoverableEntityType>  $entityTypes
     */
    public function __construct(
        public string $key,
        public string $dataType,
        public array $allowedOperators,
        public array $entityTypes,
        public bool $filterable,
        public bool $sortable,
        public ?string $requiredCapability = null,
    ) {}
}

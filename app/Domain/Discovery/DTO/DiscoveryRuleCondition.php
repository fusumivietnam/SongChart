<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use InvalidArgumentException;

final readonly class DiscoveryRuleCondition
{
    public function __construct(
        public string $field,
        public DiscoveryRuleOperator $operator,
        public mixed $value = null,
    ) {
        if ($field === '') {
            throw new InvalidArgumentException('Discovery rule field must not be empty.');
        }
    }
}

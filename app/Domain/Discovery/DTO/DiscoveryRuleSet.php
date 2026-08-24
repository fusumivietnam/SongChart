<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use InvalidArgumentException;

final readonly class DiscoveryRuleSet
{
    public const SCHEMA_VERSION = 1;

    public const MAX_CONDITIONS = 25;

    public const MAX_DEPTH = 3;

    /** @param  list<DiscoveryRuleCondition>  $conditions */
    public function __construct(
        public string $booleanOperator,
        public array $conditions,
        public int $schemaVersion = self::SCHEMA_VERSION,
    ) {
        if (! in_array($booleanOperator, ['and', 'or'], true)) {
            throw new InvalidArgumentException('Discovery rule boolean operator must be and/or.');
        }

        if ($conditions === [] || count($conditions) > self::MAX_CONDITIONS) {
            throw new InvalidArgumentException('Discovery rules require between 1 and 25 conditions.');
        }

        if ($schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Unsupported discovery rule schema version.');
        }
    }
}

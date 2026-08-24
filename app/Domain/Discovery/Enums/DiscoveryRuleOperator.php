<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Enums;

enum DiscoveryRuleOperator: string
{
    case Equal = 'eq';
    case NotEqual = 'not_eq';
    case In = 'in';
    case NotIn = 'not_in';
    case GreaterThan = 'gt';
    case GreaterThanOrEqual = 'gte';
    case LessThan = 'lt';
    case LessThanOrEqual = 'lte';
    case Exists = 'exists';
    case NotExists = 'not_exists';
}

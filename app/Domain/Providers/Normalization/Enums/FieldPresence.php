<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Enums;

enum FieldPresence: string
{
    case Missing = 'missing';
    case Unknown = 'unknown';
    case ExplicitNull = 'explicit_null';
    case Provided = 'provided';

    public function hasValue(): bool
    {
        return $this === self::Provided;
    }

    public function wasSupplied(): bool
    {
        return $this === self::Provided || $this === self::ExplicitNull;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Validation\Enums;

enum NormalizationFailureKind: string
{
    case RequiredField = 'required-field';
    case InvalidValue = 'invalid-value';
    case InvalidIdentifier = 'invalid-identifier';
    case InvalidRelationship = 'invalid-relationship';
    case UnsupportedEntity = 'unsupported-entity';
    case UnsafeUrl = 'unsafe-url';
    case PayloadTooLarge = 'payload-too-large';
}
